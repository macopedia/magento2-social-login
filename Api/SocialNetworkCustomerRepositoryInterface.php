<?php
/*
 * MIT License
 *
 * Copyright (c) 2023 Techyouknow
 */

namespace Techyouknow\SocialLogin\Api;

interface SocialNetworkCustomerRepositoryInterface
{
    public function getById($id);

    public function socialNetworkCustomerExists(array $userProfile, string $type): int;

    public function save(\Techyouknow\SocialLogin\Api\Data\SocialNetworkCustomer $socialNetworkCustomer);

    public function delete(\Techyouknow\SocialLogin\Api\Data\SocialNetworkCustomer $socialNetworkCustomer);
}