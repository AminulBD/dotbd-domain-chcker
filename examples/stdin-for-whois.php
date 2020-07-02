<?php
use AminulBD\DotBD\Finder;

require_once __DIR__ . '/../vendor/autoload.php';

$domain = fgets(STDIN);

$domain = strtolower(trim($domain));
[$name, $ext] = explode('.', $domain, 2);

$finder = new Finder;

$result = $finder->name($name)->ext(".$ext")->check();

echo json_encode([
	'domain' => $result->domain(),
	'eligible' => ($result->eligible() ? 'DOMAIN IS AVAILABLE FOR REGISTER.' : 'DOMAIN IS ALREADY REGISTED.'),
	'whois' => $result->whois(), //  if registered.
	'available' => $result->available(),
], JSON_UNESCAPED_UNICODE);

exit;


/**
- Tools required for linux server: telnetd, update-inetd. xinetd, php-cli
- Create `/etc/xinetd.d/whois` file and put code below:
- Check /etc/services if is enabled 43 port.
- enable 43 tcp in your firewall

=======
# default: on
# description: Dot BD Whois Checker Service

service whois
{
        socket_type             = stream
        protocol                = tcp
        wait                    = no
        user                    = USERNAME
        server                  = /usr/bin/php
        server_args             = /root/USERNAME/examples/stdin-for-whois.php
        log_on_success          += DURATION
        nice                    = 10
        disable                 = no
}
=====

WHMCS Integration: resources/domains/dist.whois.json

{
    "extensions": ".com.bd,.net.bd,.org.bd,.edu.bd,.co.bd,.mil.bd,.gov.bd,.ac.bd,.info.bd,.tv.bd,.sw.bd",
    "uri": "socket://YOUR_HOSTNAME_OR_IP",
    "available": "DOMAIN IS AVAILABLE FOR REGISTER."
}
 */
