<?php
namespace Framework\Models\Memcache;

use Memcached;

class FwMemcached implements MemcacheInterface
{
	private ?Memcached $conn;

    private string $host;

    private int $port;

	public function __construct(string $host, int $port)
    {
		$this->host = $host;

		$this->port = $port;
	}

	public function __destruct()
    {
		$this->disconnect();
	}

	public function connect():void
    {
		if ( empty($this->conn) ) {
			$this->conn = new Memcached();
			$this->conn->addServer($this->host, $this->port);
		}
	}

	public function disconnect():void
    {
		if ( !empty($this->conn) ) {
			$this->conn->quit();
			$this->conn = null;
		}
	}

	public function add(string $key, mixed $data, int $expire = 0):bool
    {
		$this->connect();

		return $this->conn->add($key, $data, $expire);
	}

	public function set(string $key, mixed $data, int $expire = 0):bool
    {
		$this->connect();

		return $this->conn->set($key, $data, $expire);
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
