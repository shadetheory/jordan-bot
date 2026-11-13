<?php

namespace App\Support;

class SpokenLines
{
    /**
     * Turn a stored, texty reply into lines that can be spoken.
     * Markdown, links, and list markers do not survive.
     */
    public static function fromStoredReply(string $reply): string
    {
        $text = str_replace(["\r\n", "\r"], "\n", $reply);
        $text = preg_replace('/```.*?```/s', ' ', $text) ?? $text;
        $text = preg_replace('/`([^`]+)`/', '$1', $text) ?? $text;
        $text = preg_replace('/!\[[^\]]*\]\([^)]+\)/', '', $text) ?? $text;
        $text = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '$1', $text) ?? $text;
        $text = preg_replace('/^\s{0,3}#{1,6}\s+/m', '', $text) ?? $text;
        $text = preg_replace('/^\s{0,3}>\s?/m', '', $text) ?? $text;
        $text = preg_replace('/^\s*[-*+]\s+/m', '', $text) ?? $text;
        $text = preg_replace('/^\s*\d+\.\s+/m', '', $text) ?? $text;
        $text = str_replace(['**', '__', '~~', '*', '_'], '', $text);
        $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        $paragraphs = preg_split("/\n\s*\n/", trim($text)) ?: [];
        $lines = [];

        foreach ($paragraphs as $paragraph) {
            $line = trim((string) (preg_replace('/\s+/', ' ', $paragraph) ?? ''));

            if ($line !== '') {
                $lines[] = $line;
            }
        }

        $spoken = implode(' [pause] ', $lines);

        if (mb_strlen($spoken) > 15000) {
            $spoken = mb_substr($spoken, 0, 15000);
        }

        return trim($spoken);
    }
}
