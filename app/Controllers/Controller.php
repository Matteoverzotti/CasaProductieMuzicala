<?php

class Controller {
    /**
     * Render a view template with provided data.
     *
     * @param string $view The name of the view file (without .php extension).
     * @param array $data An associative array of data to be extracted for the view.
     * @return void
     */
    protected function render(string $view, array $data = []) : void {
        if (isset($_SESSION['flash'])) {
            $data['flash'] = $_SESSION['flash'];
            unset($_SESSION['flash']);
        }
        $data['csrf_token'] = $_SESSION['csrf_token'] ?? '';
        extract($data);
        require __DIR__ . '/../Views/' . $view . '.php';
    }

    /**
     * Validate Google reCAPTCHA.
     *
     * @return bool
     */
    protected function validateRecaptcha(): bool {
        $recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';
        if (empty($recaptchaResponse)) {
            return false;
        }

        $secret = $_ENV['RECAPTCHA_SECRET_KEY'] ?? '';
        $url = 'https://www.google.com/recaptcha/api/siteverify';
        $data = [
            'secret' => $secret,
            'response' => $recaptchaResponse,
            'remoteip' => $_SERVER['REMOTE_ADDR']
        ];

        $options = [
            'http' => [
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data)
            ]
        ];

        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        if ($result === false) {
            return false;
        }

        $resultJson = json_decode($result);
        return $resultJson->success;
    }
}
