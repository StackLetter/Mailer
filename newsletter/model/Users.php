<?php

namespace Newsletter\Model;


class Users extends BaseModel{
    const TABLE = 'users';

    public function get($id, $recurse = true){
        $user = parent::get($id);
        if($recurse){
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
}
