<?php
namespace prj;

/**
 * Override default PHP session handler with custom one that uses database.
 * Motivation? i.e. share sessions between frontends (high availability)
 */
class SessionDB implements \SessionHandlerInterface
{
	private $db = null;
    private ?string $savePath = null;
    private array $hash = [];

    public function __construct($db) {
    	$this->db = $db;
    }

    public function open($savePath, $sessionName): bool
    {
        $this->savePath = ""; //$savePath;
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    #[\ReturnTypeWillChange]
    public function read($id)
    {
    	if (! self::slug($id)) user_error("invalid session.id=$id");
    	$data = $this->db->getCell("SELECT value FROM session WHERE path = ?", [$this->getPath($id)]);
        if (! $data) $data = ""; // Suppress no result here
        $data = trim($data);     // Ensure simple string..

        $this->hash[$id] = md5($data);
        return $data;
    }

    public function write($id, $data): bool
    {
    	if (! self::slug($id)) user_error("invalid session.id=$id");
        if ($data === 'a:0:{}') return true; // no storage for empty data
        $data = trim($data);
        if (isset($this->hash[$id]) && $this->hash[$id] === md5($data)) return true; // don't overwrite with no change

    	$now = time();
    	$this->db->insertUpdate("session",
    		["value" => $data, "path" => $this->getPath($id), "tm_updated" => $now],
    		["value" => $data, "tm_updated" => $now],
    	);

        $this->hash[$id] = md5($data);
        return true;
    }

    public function destroy($id): bool
    {
    	if (! self::slug($id)) user_error("invalid session.id=$id");
        $path = $this->getPath($id);
    	$aff = $this->db->delete("session", ["path" => $path]);
        if ($aff !== 1) error_log("WARN: Session(%s) could not find in DB", $path);
        return true;
    }

    #[\ReturnTypeWillChange]
    public function gc($maxlifetime)
    {
    	// TODO: Warn when getting slow?
    	$this->db->exec("DELETE FROM session WHERE tm_updated < ?", [time() - $maxlifetime]);
        return true;
    }

    public function getPath($id)
    {
        return sprintf("%s/%s", $this->savePath, $id);
    }

	private static function slug($val)
	{
		return 1 === preg_match("/^[a-z0-9_\-]{2,}$/i", $val);
	}
    public static function init($db)
    {
        ini_set("session.serialize_handler", "php_serialize"); // use text serializer
		$handler = new self($db);
		session_set_save_handler($handler, true);
    }
}
