<?php


namespace Phapi\Cache\Memcached;

use Phapi\Contract\Cache\Cache;

/**
 * Memcached
 *
 * @category Phapi
 * @package  Phapi\Cache\Memcached
 * @author   Peter Ahinko <peter@ahinko.se>
 * @license  MIT (http://opensource.org/licenses/MIT)
 * @link     https://github.com/phapi/cache-memcached
 */
class Memcached implements Cache
{

    /**
     * Cache connection
     *
     * @var \Memcached
     */
    protected $cache;

    /**
     * List of servers
     *
     * @var array
     */
    protected $servers;

    /**
     * Expiration time
     *
     * @var int
     */
    protected $expire;

    /**
     * @param array $servers List of servers
     * @param int $expire Expiration time, defaults to 3600 seconds
     * @throws \Exception
     */
    public function __construct($servers = [], $expire = 3600)
    {
        // Set expiration time
        $this->expire = $expire;

        // Create memcached object
        $this->cache = new \Memcached();

        // Check if there already are servers added, according to the manual
        // http://php.net/manual/en/memcached.addservers.php no duplication checks
        // are made. Since we have at least one connection we don't need to add
        // more servers and maybe add duplicates.
        if (count($this->cache->getServerList()) === 0) {
            // Add servers
            $this->cache->addServers($servers);
        }

        // Get server stats
        $stats = $this->cache->getStats();
        // Loop through servers
        foreach ($stats as $stat) {
            // Check if pid is more than 0, if pid is -1 connection isn't working
            if ($stat['pid'] > 0) {
                // Return true to avoid the exception below
                return true;
            }
        }

        // If we end up here we don't have a working connection. Throw an exception that
        // will be handled by the method calling this connect method. A working cache is
        // NOT a requirement for the application to run so it's important to handle the
        // exception and let the application run. Suggestion: if the exception below is
        // thrown a new NullCache should be created
        throw new \Exception('Unable to connect to Memcache(d) backend');
    }

    /**
     * Save something to the cache
     *
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public function set($key, $value)
    {
        return $this->cache->set($key, $value, $this->expire);
    }

    /**
     * Check if key is set
     *
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        return (boolean) $this->cache->get($key);
    }

    /**
     * Get value based on key
     *
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->cache->get($key);
    }

    /**
     * Remove from cache
     *
     * @param string $key
     * @return bool
     */
    public function clear($key)
    {
        return $this->cache->delete($key);
    }

    /**
     * Flush cache
     *
     * @return bool
     */
    public function flush()
    {
        return $this->cache->flush();
    }
}
