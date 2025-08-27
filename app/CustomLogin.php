<?php

namespace App;

use DiogoGPinto\AuthUIEnhancer\Pages\Auth\Concerns\HasCustomLayout;
use Filament\Pages\Auth\Login;

class CustomLogin extends Login
{
    use HasCustomLayout;
}
