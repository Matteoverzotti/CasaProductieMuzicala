<?php

class JWT {

    public static function base64UrlEncode(string $data): string {
        $b64 = base64_encode($data);
        return str_replace(['+', '/', '='], ['-', '_', ''], $b64);
    }

    public static function base64UrlDecode(string $data): string {
        $b64 = str_replace(['-', '_'], ['+', '/'], $data);
        $padding = strlen($b64) % 4;
        if ($padding > 0) {
            $b64 .= str_repeat('=', 4 - $padding);
        }
        return base64_decode($b64);
    }

    public static function encode(array $payload, string $secret, int $ttl = 3600, array $headers = []): string {
        $headers = array_merge(['typ' => 'JWT', 'alg' => 'HS256'], $headers);
        $header_encoded = self::base64UrlEncode(json_encode($headers));

        $now = time();
        $payload = array_merge($payload, [
            'iat' => $now,
            'exp' => $now + $ttl
        ]);
        $payload_encoded = self::base64UrlEncode(json_encode($payload));

        $signature = self::sign("$header_encoded.$payload_encoded", $secret);

        return "$header_encoded.$payload_encoded.$signature";
    }

    private static function sign(string $data, string $secret): string {
        $sig = hash_hmac('sha256', $data, $secret, true);
        return self::base64UrlEncode($sig);
    }

    public static function decode(string $jwt, string $secret): ?array {
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) {
            return null;
        }

        [$header_encoded, $payload_encoded, $signature_provided] = $parts;

        $signature_verified = self::sign("$header_encoded.$payload_encoded", $secret);
        if (!hash_equals($signature_verified, $signature_provided)) {
            return null;
        }

        $payload_json = self::base64UrlDecode($payload_encoded);
        $payload = json_decode($payload_json, true);

        if (isset($payload['exp']) && time() > $payload['exp']) {
            return null;
        }

        return $payload;
    }
}
