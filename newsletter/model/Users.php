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
        $tags = (int) $this->db->select('COUNT(*)', 'user_tags')->leftJoin('users', 'users.id = user_tags.user_id')->where('users.id', $uid)->fetchSingle();
        if($tags > 0){
            return true;
        }
        $questions = (int) $this->db->select('COUNT(*)', 'questions')->leftJoin('users', 'users.id = questions.owner_id')->where('users.id', $uid)->fetchSingle();
        return $questions > 0;
    }
}
