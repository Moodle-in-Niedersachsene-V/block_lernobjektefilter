<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace block_lernobjektefilter;

/**
 * Direct connection to the AI backend for keyword suggestions: Ollama (own server) or the Anthropic Claude API.
 *
 * Same approach as block_questionfilter: the backend is chosen in the plugin settings, the request runs
 * through Moodle's \curl (proxy settings and the curl security block list of the site apply), and
 * Moodle's core_ai subsystem is not used.
 *
 * @package   block_lernobjektefilter
 * @copyright 2026 Moodle in Niedersachsen e. V.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ai_connector {
    /** Endpoint of the Anthropic Messages API (fixed, never from input). */
    public const CLAUDE_URL = 'https://api.anthropic.com/v1/messages';

    /** Version header of the Anthropic API. */
    public const CLAUDE_API_VERSION = '2023-06-01';

    /** Default Claude model (documented in the setting). */
    public const CLAUDE_DEFAULT_MODEL = 'claude-haiku-4-5-20251001';

    /** Default Ollama URL and model (documented in the settings). */
    public const OLLAMA_DEFAULT_URL = 'http://localhost:11434';

    /** Default Ollama model. */
    public const OLLAMA_DEFAULT_MODEL = 'gemma3:4b';

    /** Request timeout in seconds. */
    public const TIMEOUT = 30;

    /** Maximum length of the answer in tokens. */
    public const MAX_TOKENS = 400;

    /**
     * The configured backend.
     *
     * @return string 'claude' or 'ollama'.
     */
    public static function backend(): string {
        return get_config('block_lernobjektefilter', 'ai_backend') === 'claude' ? 'claude' : 'ollama';
    }

    /**
     * Whether the configured backend has what it needs (API key for Claude, URL for Ollama).
     *
     * @return bool
     */
    public static function is_configured(): bool {
        if (self::backend() === 'claude') {
            return trim((string)get_config('block_lernobjektefilter', 'ai_claude_apikey')) !== '';
        }
        return self::ollama_url() !== '';
    }

    /**
     * Send a prompt to the configured backend and return the generated text.
     *
     * @param string $prompt Prompt.
     * @return string Generated text.
     * @throws \moodle_exception If the request fails or the answer contains no text.
     */
    public function generate(string $prompt): string {
        return self::backend() === 'claude' ? $this->call_claude($prompt) : $this->call_ollama($prompt);
    }

    /**
     * Call the Anthropic Claude API.
     *
     * @param string $prompt Prompt.
     * @return string Generated text.
     * @throws \moodle_exception If the request fails.
     */
    private function call_claude(string $prompt): string {
        $apikey = trim((string)get_config('block_lernobjektefilter', 'ai_claude_apikey'));
        $model = trim((string)get_config('block_lernobjektefilter', 'ai_claude_model')) ?: self::CLAUDE_DEFAULT_MODEL;
        if ($apikey === '') {
            throw new \moodle_exception('error_ai_unavailable', 'block_lernobjektefilter');
        }

        $payload = json_encode([
            'model'       => $model,
            'max_tokens'  => self::MAX_TOKENS,
            'temperature' => 0.2,
            'messages'    => [['role' => 'user', 'content' => $prompt]],
        ], JSON_UNESCAPED_UNICODE);
        $data = $this->post(self::CLAUDE_URL, $payload, [
            'Content-Type: application/json',
            'x-api-key: ' . $apikey,
            'anthropic-version: ' . self::CLAUDE_API_VERSION,
        ]);

        $text = '';
        foreach ((array)($data['content'] ?? []) as $block) {
            if (is_array($block) && ($block['type'] ?? '') === 'text') {
                $text .= (string)($block['text'] ?? '');
            }
        }
        if ($text === '') {
            throw new \moodle_exception('error_ai_request_failed', 'block_lernobjektefilter', '', self::error_of($data));
        }
        return $text;
    }

    /**
     * Call the Ollama API of the own server.
     *
     * @param string $prompt Prompt.
     * @return string Generated text.
     * @throws \moodle_exception If the request fails.
     */
    private function call_ollama(string $prompt): string {
        $model = trim((string)get_config('block_lernobjektefilter', 'ai_ollama_model')) ?: self::OLLAMA_DEFAULT_MODEL;
        $payload = json_encode([
            'model'   => $model,
            'prompt'  => $prompt,
            'stream'  => false,
            'format'  => 'json',
            'options' => ['temperature' => 0.2, 'num_predict' => self::MAX_TOKENS],
        ], JSON_UNESCAPED_UNICODE);
        $data = $this->post(self::ollama_url() . '/api/generate', $payload, ['Content-Type: application/json']);

        $text = (string)($data['response'] ?? '');
        if ($text === '') {
            throw new \moodle_exception('error_ai_request_failed', 'block_lernobjektefilter', '', self::error_of($data));
        }
        return $text;
    }

    /**
     * POST a JSON body through Moodle's \curl and decode the JSON answer.
     *
     * The site's curl security settings stay active (no ignoresecurity), so blocked hosts and ports are refused.
     *
     * @param string $url Target URL (fixed for Claude, from the settings for Ollama).
     * @param string $payload JSON body.
     * @param string[] $headers HTTP headers.
     * @return array Decoded answer.
     * @throws \moodle_exception If the request fails or the answer is no JSON.
     */
    private function post(string $url, string $payload, array $headers): array {
        global $CFG;
        require_once($CFG->libdir . '/filelib.php');

        $curl = new \curl();
        $curl->setHeader($headers);
        $curl->setopt([
            'CURLOPT_TIMEOUT'        => self::TIMEOUT,
            'CURLOPT_CONNECTTIMEOUT' => 10,
        ]);
        $body = $curl->post($url, $payload);
        $info = $curl->get_info();
        $status = (int)($info['http_code'] ?? 0);

        if ($curl->get_errno() || $status === 0) {
            // A blocked URL or a network error; the message never contains the API key.
            throw new \moodle_exception(
                'error_ai_request_failed',
                'block_lernobjektefilter',
                '',
                \core_text::substr(trim($curl->error ?: (string)$body), 0, 200)
            );
        }
        $data = json_decode((string)$body, true);
        if ($status >= 400 || !is_array($data)) {
            $message = is_array($data) ? self::error_of($data) : \core_text::substr((string)$body, 0, 200);
            throw new \moodle_exception('error_ai_request_failed', 'block_lernobjektefilter', '', "HTTP {$status}: {$message}");
        }
        return $data;
    }

    /**
     * Configured Ollama base URL without trailing slash.
     *
     * @return string URL, empty if not set or invalid.
     */
    private static function ollama_url(): string {
        $url = get_config('block_lernobjektefilter', 'ai_ollama_url');
        $url = $url === false ? self::OLLAMA_DEFAULT_URL : trim((string)$url);
        return rtrim(clean_param($url, PARAM_URL), '/');
    }

    /**
     * Short error text of a backend answer.
     *
     * @param array $data Decoded answer.
     * @return string Error message, shortened.
     */
    private static function error_of(array $data): string {
        $error = $data['error'] ?? '';
        $message = is_array($error) ? (string)($error['message'] ?? '') : (string)$error;
        return \core_text::substr($message !== '' ? $message : 'no text in the answer', 0, 200);
    }
}
