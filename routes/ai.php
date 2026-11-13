<?php

use App\Mcp\Servers\JordanServer;
use Laravel\Mcp\Facades\Mcp;

Mcp::local('jordan', JordanServer::class);

if (! app()->isProduction()) {
    Mcp::web('/mcp/jordan', JordanServer::class);
}
