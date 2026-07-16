<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class GoogleOAuth extends BaseConfig
{
    public string $clientId = '';

    public string $clientSecret = '';

    /**
     * Kosongkan agar otomatis memakai site_url('auth/google/callback').
     */
    public string $redirectUri = '';

    public function __construct()
    {
        parent::__construct();

        $this->clientId     = (string) env('GOOGLE_CLIENT_ID', '');
        $this->clientSecret = (string) env('GOOGLE_CLIENT_SECRET', '');
        $this->redirectUri  = (string) env('GOOGLE_REDIRECT_URI', '');
    }

    public function isConfigured(): bool
    {
        return $this->clientId !== '' && $this->clientSecret !== '';
    }
}
