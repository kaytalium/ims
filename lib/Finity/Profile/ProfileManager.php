<?php

namespace Finity\Profile;

class ProfileManager extends \Finity\Authenticate\DatabaseConnection implements UserRequestInterface{

    public function __construct(){

    }

    /**
     * All persons of the system is a user, since the user class extends a person 
     * we will then load the user construct with the new person information
     */
    public function createProfile(User $user) : User{
        //we must first check to see if the username already exist
        $userQuery = "SELECT `person_id` FROM user WHERE `username`='".$user->get_username()."'";
        
        $userResult = $this->select($userQuery);

        if(!$userResult['state']){
            //This mean this username is not in the database
            //therefore we can go ahead and create a new person
            $user->set_person_id($this->insert($user->personCreateQueryString()));

            //not that we have the person id that was just created 
            //we can go ahead and setup the user for creation

            //Create the harsh and encrypt password
            $Oauth = new \Finity\Authenticate\Oauth($user);
            $user = $Oauth->encrypt_password();
            
            //Create user and return new user_id
            $user->set_user_id($this->insert($user->userCreateQueryString()));
        }

        return $user;
    }
       
    public function updateUser(User $user) : bool{
        $query = $this->prepare($user->get_profile());
        print_ra($query);
        $this->update($query);
        return true;
         
    }

    public function deleteUser(String $userId) : bool{

        $query = "DELETE FROM `user` WHERE `person_id`='$userId'";
        $this->delete($query);

        $deqry= "DELETE FROM `person` WHERE `person_id`='$userId'";
        $this->delete($deqry);
        return true;
        
    }

    public function suspendUser(String $personId, String $status) : bool{
            $status = ($status==0?1:0);
            $upqry= "UPDATE user  SET `status`='$status' WHERE `person_id`='$personId' ";
             $result = $this->update($upqry);
             return $result['state'];
    }

    public function getAllUsers():array{
        $listofUsers = array();
        $i=0;
        //get the query string for all users in the db
        $allUsersQueryString = "SELECT `u`.`person_id`,`username`, `type`, `status`, `firstname`, `lastname` 
        FROM `user` `u`, `person` `p`, `user_type` `ut` WHERE `u`.`user_type_id`=`ut`.`user_type_id` 
        AND `p`.`person_id`=`u`.`person_id`";

        $result = $this->select($allUsersQueryString);
        
        if($result['state'])
            foreach($result['data'] as $user){
                //print_ra($user);
                $listofUsers[$i] = new User($user);
                $i++;
            }
        
        return $listofUsers;
    }

    public function getUser($person_id){

        $user = new User();
        $usersQueryString = "SELECT `u`.`person_id`,`username`, `type`, `status`, `firstname`, `lastname` 
        FROM `user` `u`, `person` `p`, `user_type` `ut` WHERE `u`.`user_type_id`=`ut`.`user_type_id` 
        AND `u`.`person_id`=`p`.`person_id` AND `u`.`person_id`='$person_id'";

        $result = $this->select($usersQueryString);

        if($result['state'])
            $user->loadUser($result['data'][0]);

        return $user;
    }

    public function setPassword($username, $password){
        //Create the harsh and encrypt password
        $Oauth = new \Finity\Authenticate\Oauth(new User);
        $crypt = $Oauth->raw_encrypt($password);

        //query
        $query = "UPDATE `user` SET `secret`='".$crypt['secret']."', `harsh`='".$crypt['harsh']."' WHERE `username`='".$username."'";
        $result = $this->update($query);
        return $result['state'];
    }

    private function prepare($paramArray){
        if(!empty($paramArray)){
            $q = 'UPDATE `person` `p`, `user` `u` SET ';
            $paramArray = array_filter($paramArray);

            $count = count($paramArray);
            $i = 2;
            echo 'Count: '.$count;
            foreach($paramArray as $key=>$value){

                if(!empty($value) && $key !='person_id' ){
                    $q .= "`".$key."`='".$value."'";

                    if($count>1 && $i<$count)
                    $q .=',';
                    
                }  
                $i++;
                                       
                
                
            }
            $q .= " WHERE `u`.`person_id`=`p`.`person_id` AND `u`.`person_id`='".$paramArray['person_id']."'";
            return $q;
        }
    }


   
}