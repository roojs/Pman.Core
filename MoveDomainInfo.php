<?php

require_once 'Pman.php';

class Pman_Core_MoveDomainInfo extends Pman
{
    static $cli_desc = "Copy appid, client_secret and server_id from core_domain_real to mail_imap_domain";

    static $cli_opts = array(
        'dry-run' => array(
            'desc' => 'Show what would be copied without making changes',
            'default' => false,
            'short' => 'd',
            'min' => 0,
            'max' => 0,
        ),
        'debug' => array(
            'desc' => 'Show detailed output during processing',
            'default' => false,
            'short' => 'v',
            'min' => 0,
            'max' => 0,
        )
    );

    function getAuth()
    {
        $ff = HTML_FlexyFramework::get();

        if (!$ff->cli) {
            die("cli only");
        }

        return true;
    }

    function get($base = '', $opts = array())
    {
        $dryRun = !empty($opts['dry-run']);
        $debug = !empty($opts['debug']);

        if ($dryRun) {
            echo "=== DRY RUN MODE - No changes will be made ===\n";
        }

        echo "Starting domain info migration from core_domain_real to mail_imap_domain...\n";

        // Get all domains from core_domain_real that have appid, client_secret, or server_id
        $domains = $this->getDomainsWithMailInfo($debug);
        
        if (empty($domains)) {
            echo "No domains found with mail information (appid, client_secret, or server_id)\n";
            $this->jok('DONE');
            return;
        }

        echo "Found " . count($domains) . " domains with mail information\n";

        // Process each domain
        $copiedCount = 0;
        $skippedCount = 0;
        $errorCount = 0;

        foreach ($domains as $domain) {
            $result = $this->copyDomainInfo($domain, $dryRun, $debug);
            
            switch ($result) {
                case 'copied':
                    $copiedCount++;
                    break;
                case 'skipped':
                    $skippedCount++;
                    break;
                case 'error':
                    $errorCount++;
                    break;
            }
        }

        echo "\nMigration Summary:\n";
        echo "  Copied: $copiedCount domains\n";
        echo "  Skipped: $skippedCount domains (already exist or no mail info)\n";
        echo "  Errors: $errorCount domains\n";

        if ($dryRun) {
            echo "=== DRY RUN COMPLETED - No changes were made ===\n";
        } else {
            echo "Migration completed successfully!\n";
        }
        
        $this->jok('DONE');
    }

    private function getDomainsWithMailInfo($debug = false)
    {
        // Use raw SQL to get domains with mail information
        // Since the DataObject might not have all fields, we'll query directly
        $sql = "
            SELECT 
                id,
                domain,
                appid,
                client_secret,
                server_id
            FROM core_domain_real 
            WHERE 
                (appid != '' AND appid IS NOT NULL) OR 
                (client_secret != '' AND client_secret IS NOT NULL) OR 
                server_id > 0
            ORDER BY domain
        ";

        $db = DB_DataObject::factory('core_domain_real');
        $result = $db->query($sql);
        
        $domains = array();
        while ($row = $result->fetchRow()) {
            $domains[] = (object) $row;
        }

        if ($debug) {
            echo "Domains with mail info:\n";
            foreach ($domains as $domain) {
                echo "  {$domain->domain} (ID: {$domain->id}) - appid: '{$domain->appid}', client_secret: '{$domain->client_secret}', server_id: {$domain->server_id}\n";
            }
        }

        return $domains;
    }

    private function copyDomainInfo($domain, $dryRun = false, $debug = false)
    {
        // Check if domain already exists in mail_imap_domain
        $mailDomain = DB_DataObject::factory('mail_imap_domain');
        if ($mailDomain->get('domainname', $domain->domain)) {
            if ($debug) {
                echo "  Skipping {$domain->domain} - already exists in mail_imap_domain\n";
            }
            return 'skipped';
        }

        // Check if domain has any mail information to copy
        $hasMailInfo = !empty($domain->appid) || !empty($domain->client_secret) || $domain->server_id > 0;
        
        if (!$hasMailInfo) {
            if ($debug) {
                echo "  Skipping {$domain->domain} - no mail information to copy\n";
            }
            return 'skipped';
        }

        if ($debug) {
            echo "  Copying {$domain->domain}:\n";
            echo "    appid: '{$domain->appid}'\n";
            echo "    client_secret: '{$domain->client_secret}'\n";
            echo "    server_id: {$domain->server_id}\n";
        }

        if ($dryRun) {
            echo "  DRY RUN: Would create mail_imap_domain record for {$domain->domain}\n";
            return 'copied';
        }

        // Create new mail_imap_domain record
        $mailDomain = DB_DataObject::factory('mail_imap_domain');
        $mailDomain->domainname = $domain->domain;
        $mailDomain->appid = $domain->appid ?: '';
        $mailDomain->client_secret = $domain->client_secret ?: '';
        $mailDomain->server_id = $domain->server_id ?: 0;

        if ($mailDomain->insert()) {
            echo "  Created mail_imap_domain record for {$domain->domain} (ID: {$mailDomain->id})\n";
            return 'copied';
        } else {
            echo "  ERROR: Failed to create mail_imap_domain record for {$domain->domain}\n";
            return 'error';
        }
    }
}
