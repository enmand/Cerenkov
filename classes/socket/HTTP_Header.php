<?php
/**
 * HTTP_Header.php
 * 
 * @author Cerenkov Group
 * @copyright Cerenkov 2007
 * @package classes/socket
 */
class HTTP_Header
{
	private $sock;
	private $host;
	private $port;
	private $response;
	
	public function __construct($host, $port = 80)
	{
		$this->host = $host;
		$this->port = $port;
		$this->createSocket($host, $port);
	}
	
	public function createSocket($host, $port = 80)
	{
		if(!($this->sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)))
			throw new SocketException("Could not create socket connection. Socket message was: " . socket_strerror(socket_last_error()));
		if(!(socket_connect($this->sock, $host, $port)))
			throw new SocketException("Could not connect to $host:$port. Socket message was: " . socket_strerror(socket_last_error()));
	}
	
	public function POST($script, $data, $length=4048)
	{
		$send = "POST $script HTTP/1.1\r\n";
		$send .= "Host: $this->host\r\n";
		$send .= "User-agent: Cerenkov" . F_VERSION . "\r\n";
		$send .= "Accept: " . $_SERVER['HTTP_ACCEPT'] . "\r\n";
		$send .= "Accept-Language: " . $_SERVER['HTTP_ACCEPT_LANGUAGE'] . "\r\n";
		$send .= "Accept-Encoding: " . $_SERVER['HTTP_ACCEPT_ENCODING'] . "\r\n";
		$send .= "Accept-Chatset: " . $_SERVER['HTTP_ACCEPT_CHARSET'] . "\r\n";
		$send .= "Keep-Alive: " . $_SERVER['HTTP_KEEP_ALIVE'] . "\r\n";
		$send .= "Connection: " . $_SERVER['HTTP_CONNECTION'] . "\r\n";
		if(isset($_SERVER['CONTENT_TYPE']))
			$send .= "Content-Type: " . $_SERVER['CONTENT_TYPE'] . "\r\n";
		if(isset($_SERVER['HTTP_REFERER']))
			$send .= "Referer: " . $_SERVER['HTTP_REFERER'] . "\r\n";
		$send .= "Cookie: " . urlencode($_SERVER['HTTP_COOKIE']) . "\r\n";
		$send .= "Content-length: ". strlen($data) . "\r\n\r\n";
		$send .= $data . "\r\n";
		socket_write($this->sock, $send, strlen($send));
		$this->response = socket_read($this->sock, $length);
		$headers = explode("\r\n", $this->response);
		$this->newSocket($this->host, $this->port);	
		$header = array_search("0", $headers);
		return @$headers[$header - 1];
	}
	
	public function newSocket($host, $port)
	{
		socket_close($this->sock);
		$this->createSocket($this->host, $this->port);
	}
	
	public function __destruct()
	{
		socket_close($this->sock);
	}
}