<?php

namespace Newsletter\Model;


class Users extends BaseModel{
    const TABLE = 'users';

    public function get($id, $recurse = true){
        $user = parent::get($id);
        if($recurse && $user){
            $user->site = $this->model->sites->get($user->site_id);
        }
        return $user;
    }

    /**
     * Returns users which are registered for a newsletter.
     * @param $frequency Newsletter frequency
     * @return \Neevo\Result
     */
    public function getRegistered($frequency = NULL){
        return $this->db->select('users.*, accounts.email AS email', static::TABLE)
            ->leftJoin('accounts', 'accounts.id = users.account_id')
            ->where('account_id IS %l', 'NOT NULL')
            ->if($frequency)
                ->where('frequency', $frequency)
            ->end();
    }


    public function isActive($uid){
        $driver = $this->db->getConnection()->getDriver();
        $resource = $driver->runQuery("
        SELECT SUM(cnt) as total FROM (
            WITH the_user AS (SELECT " . (int) $uid . " as id)
            SELECT COUNT(*) as cnt FROM questions WHERE owner_id IN (SELECT id FROM the_user) UNION
            SELECT COUNT(*) AS cnt FROM answers WHERE owner_id IN (SELECT id FROM the_user) UNION
            SELECT COUNT(*) AS cnt FROM comments WHERE owner_id IN (SELECT id FROM the_user) UNION
            SELECT COUNT(*) AS cnt FROM user_favorites WHERE user_id IN (SELECT id FROM the_user) UNION
            SELECT COUNT(*) AS cnt FROM evaluation_newsletters e
            LEFT JOIN newsletters n ON n.id = e.newsletter_id
            WHERE e.content_type IN ('question', 'answer') AND e.user_response_type IN ('click', 'feedback')
            AND n.user_id IN (SELECT id FROM the_user)
            ) united");
        if(!$resource){
            return false;
        }
        $row = $driver->fetch($resource);
        return $row === false ? false : ((bool) $row['total'] ?? false);
    }
}
