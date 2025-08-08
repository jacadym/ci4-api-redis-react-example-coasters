<?php

if (! function_exists('check_ip')) {
    /**
     * @param string|array $ips
     */
    function check_ip(string $requestIp, $ips): bool
    {
        if (!\is_array($ips)) {
            $ips = [$ips];
        }

        foreach ($ips as $ipMask) {
            $separator = substr_count($requestIp, ':') > 1 ? ':' : '.';
            if (! str_contains($ipMask, $separator)) {
                continue;
            }
            if (! str_contains($ipMask, '/')) {
                if ($ipMask === $requestIp) {
                    return true;
                }
                continue;
            }

            if ($separator === ':') {
                // Make sure we're having the "full" IPv6 format
                $ip = explode(':', str_replace('::', str_repeat(':', 9 - substr_count($requestIp, ':')), $requestIp));
                for ($j = 0; $j < 8; $j++) {
                    $ip[$j] = intval($ip[$j], 16);
                }
                $sprintf = '%016b%016b%016b%016b%016b%016b%016b%016b';
            } else {
                $ip = explode('.', $requestIp);
                $sprintf = '%08b%08b%08b%08b';
            }

            $ip = vsprintf($sprintf, $ip);

            // Split the netmask length off the network address
            sscanf($ipMask, '%[^/]/%d', $netAddr, $maskLen);

            // Again, an IPv6 address is most likely in a compressed form
            if ($separator === ':') {
                $netAddr = explode(':', str_replace('::', str_repeat(':', 9 - substr_count($netAddr, ':')), $netAddr));

                for ($i = 0; $i < 8; $i++) {
                    $netAddr[$i] = intval($netAddr[$i], 16);
                }
            } else {
                $netAddr = explode('.', $netAddr);
            }

            // Convert to binary and finally compare
            if (strncmp($ip, vsprintf($sprintf, $netAddr), $maskLen) === 0) {
                return true;
            }
        }

        return false;
    }
}
