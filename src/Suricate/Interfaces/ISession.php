<?php
namespace Suricate\Interfaces;

interface ISession
{
    public function getInstance();

    public function getId();

    public function regenerate();

    public function read($key);

    public function write($key, $data);

    public function destroy($key);

    public function close();
}
