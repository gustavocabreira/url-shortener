<?php

declare(strict_types=1);

use App\Helpers\UserAgentParser;

it('parses Chrome user agent correctly', function (): void {
    $ua = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.5735.110 Safari/537.36';
    $parser = new UserAgentParser($ua);

    expect($parser->browser())->toBe('Chrome');
    expect($parser->version())->toBe('114.0.5735.110');
    expect($parser->platform())->toBe('Windows');
    expect($parser->device())->toBe('Desktop');
});

it('parses Firefox on Linux correctly', function (): void {
    $ua = 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:109.0) Gecko/20100101 Firefox/114.0';
    $parser = new UserAgentParser($ua);

    expect($parser->browser())->toBe('Firefox');
    expect($parser->version())->toBe('114.0');
    expect($parser->platform())->toBe('Linux');
    expect($parser->device())->toBe('Desktop');
});

it('parses Safari on iPhone correctly', function (): void {
    $ua = 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.5 Mobile/15E148 Safari/604.1';
    $parser = new UserAgentParser($ua);

    expect($parser->browser())->toBe('Safari');
    expect($parser->version())->toBe('16.5');
    expect($parser->platform())->toBe('iOS');
    expect($parser->device())->toBe('Mobile');
});

it('parses Edge browser correctly', function (): void {
    $ua = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36 Edg/114.0.1823.51';
    $parser = new UserAgentParser($ua);

    expect($parser->browser())->toBe('Edge');
    expect($parser->version())->toBe('114.0.1823.51');
    expect($parser->platform())->toBe('Windows');
    expect($parser->device())->toBe('Desktop');
});

it('returns unknown for invalid user agent', function (): void {
    $ua = '🤖 UnknownBot';
    $parser = new UserAgentParser($ua);

    expect($parser->browser())->toBe('Unknown');
    expect($parser->version())->toBe('Unknown');
    expect($parser->platform())->toBe('Unknown');
    expect($parser->device())->toBe('Desktop');
});
