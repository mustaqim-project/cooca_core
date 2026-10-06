<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Mcp\Auth\McpTokenAuthenticator;
use App\Domain\Mcp\Protocol\McpProtocolEngine;
use App\Models\Business;
use App\Models\BusinessMembership;
use App\Support\Context;
use Illuminate\Console\Command;

final class McpServeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mcp:serve
                            {--token= : MCP Bearer Token for tenant authentication}
                            {--business= : Specific business ID if running as system developer}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start the COOCA Model Context Protocol (MCP) server in local Stdio mode';

    public function __construct(
        private readonly McpProtocolEngine $engine = new McpProtocolEngine(),
        private readonly McpTokenAuthenticator $authenticator = new McpTokenAuthenticator()
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $rawToken = (string) $this->option('token');
        $token = null;

        if ($rawToken !== '') {
            $token = $this->authenticator->authenticate($rawToken);
            if ($token === null) {
                $this->error("Invalid or inactive MCP token provided.");
                return self::FAILURE;
            }
        } elseif ($bizId = (string) $this->option('business')) {
            $business = Business::find($bizId);
            if ($business !== null) {
                $membership = BusinessMembership::where('business_id', $business->id)->first();
                Context::setBusiness($business, $membership);
            }
        }

        // Loop over stdin lines
        while (! feof(STDIN)) {
            $line = fgets(STDIN);
            if ($line === false || trim($line) === '') {
                continue;
            }

            $payload = json_decode($line, true);
            if (! is_array($payload)) {
                continue;
            }

            $response = $this->engine->handle($payload, $token);
            if ($response !== null) {
                fwrite(STDOUT, json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n");
                fflush(STDOUT);
            }
        }

        return self::SUCCESS;
    }
}
