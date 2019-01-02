<?php
namespace dokuwiki\discussion;
use Doctrine\DBAL\Driver\PDOException;
use PDO;
class discussionDAO
{
    public $settings;
    private $pdo;
    public function __construct()
    {
        require_once dirname(__FILE__).'/settings.php';
        $dsn = "mysql:host=".$this->settings['host'].
            ";dbname=".$this->settings['dbname'].
            ";port=".$this->settings['port'].
            ";charset=".$this->settings['charset'];
        try {
            $this->pdo = new PDO($dsn, $this->settings['username'], $this->settings['password']);
        } catch ( PDOException $e) {
            echo $e->getMessage();
            exit;
        }
    }

    /**
     * Save users' comment into DB
     *
     * @param $data   the comment data
     * @param $cid  comment id
     * @param $xhtml   the content of comment
     * @param $username username
     * @param $parent parent comment id
     * @param $ID pageid
     * @return bool
     */
    public function insertComment($data, $cid, $xhtml, $username, $parent, $ID, $userid) {
        $sql = 'insert into '. $this->settings['comment'].' (hash, comment, time, username, pageid, parent, display, deleted, parentname, userid)
            values
                (:hash, :comment, null, :username, :pageid, :parent, true, false, :parentname, :userid)';
        try {
            $parentname = null;
            if (isset($parent)) {
                $parentname = $data['comments'][$parent]['user']['name'];
            }
            $statement = $this->pdo->prepare($sql);
            $statement->bindValue(':hash', $cid);
            $statement->bindValue(':comment', $xhtml);
            $statement->bindValue(':username', $username);
            $statement->bindValue(':pageid', $ID);
            $statement->bindValue(':parent', $parent);
            $statement->bindValue(':parentname', $parentname); // parent id
            $statement->bindValue(':userid', $userid);
            $result = $statement->execute();
            return $result;
        }
        catch (PDOException $e) {
            echo $e->getMessage();
            return false;
        }

    }

    /**
     * delete comment in DB
     *
     * @param $cid comment id
     * @return bool
     */
    public function delComment($cid) {
        try {
            $sql = 'delete from '.$this->settings['comment']. ' where hash = :hash';
            $statement = $this->pdo->prepare($sql);
            $statement->bindValue(':hash', $cid);
            $result = $statement->execute();
            return $result;
        }
        catch (PDOException $e) {
            echo $e->getMessage();
            return false;
        }
    }

    /**
     * get comment data
     *
     * @param $ID page id
     * @return bool|array
     */
    public function selectData($ID) {
        try {
            $sql = 'select * from '.$this->settings['comment']. ' where pageid = :pageid order by time desc' ;
            $statement = $this->pdo->prepare($sql);
            $statement->bindValue(':pageid', $ID);
            $statement->execute();
            $res = $statement->fetchAll(PDO::FETCH_ASSOC);
            return $res;
        }
        catch (PDOException $e) {
            echo $e->getMessage();
            return false;
        }
    }

}