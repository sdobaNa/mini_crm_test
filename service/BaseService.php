<?php

interface BaseService{
    public function gteOne($id);
    public function save($rows);
    public function update($rows);
    public function delete($id);
    public function getAll();
}