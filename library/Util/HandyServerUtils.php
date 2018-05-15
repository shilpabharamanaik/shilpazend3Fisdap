<?php
/**
 * Utilities for dealing with server stuff.
 */
final class Util_HandyServerUtils
{
    public static function get_fisdap_members1_url_root()
    {
        if (!empty($_SERVER['HTTPS'])) {
            $root = 'https://';
        } else {
            $root = 'http://';
        }
        
        // reverse domain part order to work backwards from the TLD
        $domain_parts = array_reverse(explode('.', $_SERVER['HTTP_HOST']));

        $host_info = array();

        $host_info['tld'] = $domain_parts[0];
        $host_info['domain'] = $domain_parts[1];

        $host_info['hostname'] = 'members1';
    
        if (count($domain_parts) == 5) {
            $host_info['user'] = $domain_parts[3];
            $host_info['project'] = $domain_parts[4];

            $fqdn = "{$host_info['project']}.{$host_info['user']}.{$host_info['hostname']}.{$host_info['domain']}.{$host_info['tld']}";
        } elseif (count($domain_parts) == 4) {
            $fqdn = "{$host_info['hostname']}.local.{$host_info['domain']}.{$host_info['tld']}";
        } else {
            $fqdn = "{$host_info['hostname']}.{$host_info['domain']}.{$host_info['tld']}";
        }

        $fisdap_members1_url_root = $root . $fqdn . "/";

        return $fisdap_members1_url_root;
    }

    /**
     * Get the full base URL for the server. PLEASE NOTE: will not work for CLI scripts. Use \Zend_Registry::get('host') instead
     * @return string
     */
    public static function getCurrentServerRoot()
    {
        if (!empty($_SERVER['HTTPS'])) {
            $root = 'https://';
        } else {
            $root = 'http://';
        }
        
        // reverse domain part order to work backwards from the TLD
        $domain_parts = array_reverse(explode('.', $_SERVER['HTTP_HOST']));

        $host_info = array();

        $host_info['tld'] = $domain_parts[0];
        $host_info['domain'] = $domain_parts[1];

        $host_info['hostname'] = 'members';
    
        if (count($domain_parts) == 5) {
            $host_info['user'] = $domain_parts[3];
            $host_info['project'] = $domain_parts[4];

            $fqdn = "{$host_info['project']}.{$host_info['user']}.{$host_info['hostname']}.{$host_info['domain']}.{$host_info['tld']}";
        } else {
            $fqdn = "{$host_info['hostname']}.{$host_info['domain']}.{$host_info['tld']}";
        }

        $url_root = $root . $fqdn . "/";

        return $url_root;
    }

    public static function get_server()
    {
 
// Util_HandyServerUtils::get_server()

        /*
        live http://www.fisdap.net/whats_new/open_airways
        dev  http://www.fisdapdev.net/whats_new/open_airways
        qa   http://www.fisdapqa.net/whats_new/open_airways
                                      ||
                                      ||
                                      \|___ return this term
                                                    from: $_SERVER['SERVER_NAME'] = f20closedbetaprep.cpond.members.fisdapdev.net
        */
    
        // reverse domain part order to work backwards from the TLD
        $domain_parts = array_reverse(explode('.', $_SERVER['HTTP_HOST']));
        return $domain_parts[1];
    }
    
    public static function get_fisdap_content_url_root()
    {
        if (!empty($_SERVER['HTTPS'])) {
            $root = 'https://';
        } else {
            $root = 'http://';
        }

        // reverse domain part order to work backwards from the TLD
        $domain_parts = array_reverse(explode('.', $_SERVER['HTTP_HOST']));

        $host_info = array();

        $host_info['tld'] = $domain_parts[0];
        $host_info['domain'] = $domain_parts[1];

        $host_info['hostname'] = 'www';

        $fqdn = "{$host_info['hostname']}.{$host_info['domain']}.{$host_info['tld']}/";

        $fisdap_content_url_root = $root . $fqdn;

        return $fisdap_content_url_root;
    }
}
