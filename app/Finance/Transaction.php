<?php

namespace App\Finance;

/**
 * One Firestore read-write transaction. Every read comes before the first
 * write (Firestore's rule); writes are collected and committed together by
 * Firestore::transaction().
 */
class Transaction
{
    private $db;
    private $id;
    private $writes = [];

    public function __construct(Firestore $db, $id)
    {
        $this->db = $db;
        $this->id = $id;
    }

    public function id()
    {
        return $this->id;
    }

    public function writes()
    {
        return $this->writes;
    }

    public function get($path)
    {
        $this->assertReading();
        return $this->db->get($path, $this->id);
    }

    public function query($collection, array $where = [], array $orderBy = [], $limit = null)
    {
        $this->assertReading();
        return $this->db->query($collection, $where, $orderBy, $limit, $this->id);
    }

    public function set($path, array $data)
    {
        $this->writes[] = $this->db->write('set', $path, $data);
    }

    public function create($path, array $data)
    {
        $this->writes[] = $this->db->write('create', $path, $data);
    }

    /** Changes the given field paths of an existing document. */
    public function update($path, array $fields)
    {
        $this->writes[] = $this->db->write('update', $path, $fields);
    }

    private function assertReading()
    {
        if ($this->writes) {
            throw new \LogicException('Firestore transactions must read everything before the first write.');
        }
    }
}
