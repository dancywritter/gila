<?php
namespace core\models;

class widget
{

    function __construct ()
    {

    }

    static function getById($id)
    {
        global $db;
        $res = $db->query("SELECT * FROM widget WHERE id=?",$id);
        return mysqli_fetch_object($res);
    }
}