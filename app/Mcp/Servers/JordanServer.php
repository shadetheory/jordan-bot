<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\AskJordanTool;
use App\Mcp\Tools\DecideSmallTool;
use App\Mcp\Tools\SearchCardsTool;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('jordan')]
#[Version('0.1.0')]
#[Instructions('Private Jordan voice. Ask for an answer in his mouth, pull cards, or get a small call (park/later/nah/try_this). Not Lexi. Not Mattermost. Does not act.')]
class JordanServer extends Server
{
    protected array $tools = [
        AskJordanTool::class,
        SearchCardsTool::class,
        DecideSmallTool::class,
    ];
}
