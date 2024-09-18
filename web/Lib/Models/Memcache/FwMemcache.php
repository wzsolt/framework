<?php
namespace Framework\Models\Memcache;

use Memcache;

class FwMemcache implements MemcacheInterface
{
    private ?Memcache $conn;

	private string $host;

    private int $port;

    private bool $compress;

	public function __construct($host, $port, $compress = false)
    {
		$this->host = $host;

		$this->port = $port;

		$this->compress = ($compress === false) ? false : MEMCACHE_COMPRESSED;
	}

	public function __destruct()
    {
		$this->disconnect();
	}

	public function connect():void
    {
		if ( empty($this->conn) ) {
			$this->conn = new Memcache();
			$this->conn->connect($this->host, $this->port);
		}
	}

	public function disconnect():void
    {
		if ( !empty($this->conn) ) {
			$this->conn->close();
			$this->conn = null;
		}
	}

	public function add(string $key, mixed $data, int $expire = 0):bool
    {
		$this->connect();

		return $this->conn->add($key, $data, $this->compress, $expire);
	}

	public function set(string $key, mixed $data, int $expire = 0):bool
    {
		$this->connect();

		return $this->conn->set($key, $data, $this->compress, $expire);
	}

	public function get(string $key):false|array|string
    {
		$this->connect();

		return $this->conn->get($key);
	}

	public function increment(string $key, mixed $value):int|false
    {
		$this->connect();

		return $this->conn->increment($key, $value);
	}

	public function delete(string $key):bool
    {
		$this->connect();

		return $this->conn->delete($key);
	}

}
