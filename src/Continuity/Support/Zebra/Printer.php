<?php

namespace Continuity\Support\Zebra;

use Continuity\Support\Zebra\Exceptions\ZebraConnectException;
use Continuity\Support\Zebra\Exceptions\ZebraDisconnectException;
use Continuity\Support\Zebra\Exceptions\ZebraException;

class Printer
{
    /** @var string */
    private $_host;

    /** @var int */
    private $_port;

    /** @var int */
    private $_errorNo;

    /** @var string */
    private $_errorStr;

    /** @var int */
    private $_timeout;

    /** @var */
    private $_socket;

    /**
     * Printer constructor.
     * @param $host
     * @param int $port
     * @param int $timeout
     */
    public function __construct(string $host, int $port = 9100, int $timeout = 30)
    {
        $this->_host = $host;
        $this->_port = $port;
        $this->_timeout = $timeout;
    }

    /**
     * Connect to printer
     *
     * @throws ZebraConnectException
     */
    public function connect(): void
    {
        $this->_socket = fsockopen($this->_host, $this->_port,
            $this->_errorNo, $this->_errorStr, $this->_timeout);

        if ($this->_socket === false) {
            throw new ZebraConnectException($this->_errorStr, $this->_errorNo);
        }
    }

    /**
     * @param string $host
     * @param string $template
     * @param array $parameters
     * @param int $port
     * @param int $timeout
     * @throws ZebraConnectException
     * @throws ZebraDisconnectException
     * @throws ZebraException
     */
    static public function printSingle(string $host, string $template, array $parameters = [], int $port = 9100, $timeout = 30): void
    {
        try {
            $printer = new self($host, $port, $timeout);
            $printer->connect();
            $printer->print($template, $parameters);
        } finally {
            if ($printer->isConnected()) {
                $printer->disconnect();
            }
        }
    }

    /**
     * @param string $template
     * @param array $parameters
     * @throws ZebraException
     */
    public function print(string $template, array $parameters = []): void
    {
        $out = str_replace(array_keys($parameters), array_values($parameters), $template);

        if (fwrite($this->_socket, $out) === false) {
            throw new ZebraException();
        }
    }

    /**
     * Check if printer is connected
     *
     * @return bool
     */
    public function isConnected(): bool
    {
        return ($this->_socket == false) ? false : true;
    }

    /**
     * @return bool
     * @throws ZebraDisconnectException
     */
    public function disconnect(): void
    {
        if (fclose($this->_socket) === false) {
            throw new ZebraDisconnectException();
        }
    }
}
