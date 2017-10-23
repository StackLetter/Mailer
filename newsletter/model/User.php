<?php

namespace Newsletter\Model;


class User extends BaseModel{
    const TABLE = 'users';

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
