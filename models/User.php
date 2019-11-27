<?php
/**
 * Heating Support System
 *
 * @category   Application_Core
 * @package    heating-system
 * @author     Suman Barua
 * @developer  Suman Barua <sumanbarua576@gmail.com>
 */

/**
 * @category   Application_Core
 * @package    heating-system
 */

class Application_Model_User extends Zend_Db_Table_Row_Abstract
{
    public function getIdentity(){
        return (int) $this->user_id;
    }

    public function getTitle(){
        if($this->isSuperAdmin()){
            return 'Super Admin';
        }elseif($this->isAdmin()){
            return 'Admin';
        }elseif($this->isModerator()){
            return 'Moderator';
        }elseif($this->isUser()){
            return "{$this->firstname} {$this->lastname}";
        }
    }

    public function getUserType(){
        if($this->isSuperAdmin()){
            return 'superadmin';
        }elseif($this->isAdmin()){
            return 'admin';
        }elseif($this->isModerator()){
            return 'moderator';
        }elseif($this->isUser()){
            return 'user';
        }
    }

    public function getHref($params = array()){
        if($this->isModerator() ||
                $this->isAdmin() || $this->isSuperAdmin()){
            $route = 'admin_dashboard';
        }else{
            $route = 'user_dashboard';
        }

        $params = array_merge(array(
            'route' => $route,
            'reset' => true
            //'id' => $profileAddress,
        ), $params);
        $route = $params['route'];
        $reset = $params['reset'];
        unset($params['route']);
        unset($params['reset']);
        return Zend_Controller_Front::getInstance()->getRouter()
                    ->assemble($params, $route, $reset);
    }

    public function getDashBoardLink($params = array()){
        if($this->isModerator() ||
                $this->isAdmin() || $this->isSuperAdmin()){
            $route = 'admin_dashboard';
        }else{
            $route = 'user_dashboard';
        }

        $params = array_merge(array(
            'route' => $route,
            'reset' => true
            //'id' => $profileAddress,
        ), $params);
        $route = $params['route'];
        $reset = $params['reset'];
        unset($params['route']);
        unset($params['reset']);
        return Zend_Controller_Front::getInstance()->getRouter()
                    ->assemble($params, $route, $reset);
    }

    public function getEmail(){
        return $this->email;
    }

    public function getLevel(){
        return $this->level;
    }

    public function isSuperAdmin(){
        if($this->level == 1){
            return true;
        }

        return false;
    }

    public function isAdmin(){
        if($this->level == 2){
            return true;
        }

        return false;
    }

    public function isModerator(){
        if($this->level == 3){
            return true;
        }

        return false;
    }

    public function isUser(){
        if($this->level == 4){
            return true;
        }

        return false;
    }
    
    public function isTechnician(){
        return $this->technician;
    }
    
    public function canTechnicianViewTask($item = null){
        if($item){
            return (boolean) ($this->user_id == $item->technician_id);
        }
        
        return false;
    }
    
    public function isOwner($item = null){
        if($item && isset($item->user_id) 
                && $this->user_id == $item->user_id){
            return true;
        }
        
        return false;
    }
    
    public function isActive(){
        return $this->active;
    }
    
    public function isActiveWord(){
        $active = $this->active;
        if($active==1){return '<span class="status green">Active</span>';}
        elseif($active==0){return '<span class="status red">Inactive</span>';}
        return null;
    }

    public function getAddress(){
        return "{$this->address}, {$this->city_name}, {$this->postcode}, {$this->country_name}";
    }
    
    public function getLastLogin(){
        return $this->lastlogin;
    }

    public function getResetKey(){
        return $this->resetkey;
    }

    public function sendResetPasswordLink(){
        // create and save reset key
        $timestamp = time();
        $key = md5("{$this->salt}{$this->email}{$timestamp}");
        $this->resetkey = $key;
        $this->save();

        // dont send email if localhost
        if($_SERVER['HTTP_HOST'] == 'localhost'){return $key;}
        
        // prepare mail params
        $view = new Zend_View();
        $resetPasswordLink = "http://{$_SERVER['HTTP_HOST']}{$view->url(array('key' => $key), 'reset_password', true)}";
        $mailParams = array(
            'to' => $this->getEmail(),
            'from' => ' info@xtremeonecoder.com',
            'recipient' => $this->getTitle(),
            'sender' => 'Heating System',
            'subject' => 'Account activation and reset password request',
            'messagebody' => "
                Dear {$this->getTitle()},

                You have requested for your account activation and reset password.

                Here is the link to do that, please click on the link below -

                <a href='{$resetPasswordLink}'>{$resetPasswordLink}</a>

                Best Regards,
                Heating System
            ",
            //'messagebody' => nl2br(html_entity_decode(nl2br($this->getMessage()), ENT_QUOTES)),
            'mailtype' => 'html'
        );

        // sent mail
        $mail = Zend_Controller_Action_HelperBroker::getStaticHelper('Mail');
        $mail->send($mailParams);

        // return key
        return $key;
    }

    public function sendActiveAccountLink(){
        // create and save active key
        $timestamp = time();
        $key = md5("{$this->salt}{$this->email}{$timestamp}");
        $this->activekey = $key;
        $this->save();

        // dont send email if localhost
        if($_SERVER['HTTP_HOST'] == 'localhost'){return $key;}
        
        // prepare mail params
        $view = new Zend_View();
        $activationLink = "http://{$_SERVER['HTTP_HOST']}{$view->url(array('key' => $key), 'activate_account', true)}";
        $mailParams = array(
            'to' => $this->getEmail(),
            'from' => ' info@xtremeonecoder.com',
            'recipient' => $this->getTitle(),
            'sender' => 'Heating System',
            'subject' => 'Account activation and email confirmation!',
            'messagebody' => "
                Dear {$this->getTitle()},

                You have successfully registered your account with us. Now you have to activate your account by clicking on the below link.

                Here is the link to do that, please click on the link below -

                <a href='{$activationLink}'>{$activationLink}</a>

                Best Regards,
                Heating System
            ",
            //'messagebody' => nl2br(html_entity_decode(nl2br($this->getMessage()), ENT_QUOTES)),
            'mailtype' => 'html'
        );

        // sent mail
        $mail = Zend_Controller_Action_HelperBroker::getStaticHelper('Mail');
        $mail->send($mailParams);

        // return key
        return $key;
    }
    
    public function sendCreateTicketNotification($reference = null){
        // dont send email if localhost
        if($_SERVER['HTTP_HOST'] == 'localhost'){return null;}
        
        // prepare mail params
        $view = new Zend_View();
        $mailParams = array(
            'to' => $this->getEmail(),
            'from' => ' info@xtremeonecoder.com',
            'recipient' => $this->getTitle(),
            'sender' => 'Heating System',
            'subject' => 'Support ticket creation confirmation!',
            'messagebody' => "
                Dear {$this->getTitle()},

                You have successfully raised a support ticket with us. Your support ticket reference number is: {$reference}.
                
                We will review your ticket and response to you as soon as possible.
                
                Best Regards,
                Heating System
            ",
            //'messagebody' => nl2br(html_entity_decode(nl2br($this->getMessage()), ENT_QUOTES)),
            'mailtype' => 'html'
        );

        // sent mail
        $mail = Zend_Controller_Action_HelperBroker::getStaticHelper('Mail');
        $mail->send($mailParams);
    }
    
    public function notifyScheduleToMember($technician = null, $ticket = null, $schedule = null, $modified = false){        
        if($technician == null || $ticket == null || $schedule == null){
            return null;
        }
        
        // dont send email if localhost
        if($_SERVER['HTTP_HOST'] == 'localhost'){return null;}
        
        // prepare mail params
        $view = new Zend_View();
        $subject = 'Notification of service scheduled!';
        $bodyText = "Your support ticket has been allocated to a technician and the details are as follows -";
        if($modified){
            $subject = 'Notification of service rescheduled!';
            $bodyText = "Your support ticket has been rescheduled and the details are as follows -";            
        }
        $mailParams = array(
            'to' => $this->getEmail(),
            'from' => ' info@xtremeonecoder.com',
            'recipient' => $this->getTitle(),
            'sender' => 'Heating System',
            'subject' => $subject,
            'messagebody' => "
                Dear <strong>{$this->getTitle()}</strong>,

                {$bodyText}
                
                <strong>Ticket Reference:</strong> #{$ticket->getReference()}
                <strong>Ticket Title:</strong> {$ticket->getTitle()}
                <strong>Technician Name:</strong> {$technician->getTitle()}
                <strong>Phone Number:</strong> {$technician->phone_no}
                <strong>Mobile Number:</strong> {$technician->phone_no}
                <strong>Email Address:</strong> {$technician->email}
                <strong>Scheduled Date:</strong> {$schedule->scheduled_date}    
                <strong>Starting Time:</strong> {$schedule->from_time}    
                <strong>Finishing Time:</strong> {$schedule->to_time}    
                
                <strong>Task Allocator's Comment:</strong>
                {$schedule->getDescription()}
                
                Please be at home on due date and time and notify us about the progress.
                
                Best Regards,
                Heating System
            ",
            //'messagebody' => nl2br(html_entity_decode(nl2br($this->getMessage()), ENT_QUOTES)),
            'mailtype' => 'html'
        );

        // sent mail
        $mail = Zend_Controller_Action_HelperBroker::getStaticHelper('Mail');
        $mail->send($mailParams);
    }
    
    public function notifyScheduleToTechnician($member = null, $ticket = null, $schedule = null, $modified = false){        
        if($member == null || $ticket == null || $schedule == null){
            return null;
        }
        
        // dont send email if localhost
        if($_SERVER['HTTP_HOST'] == 'localhost'){return null;}
        
        // prepare mail params
        $view = new Zend_View();
        $subject = 'Notification of new task allocation!';
        $bodyText = "A new task has been allocated for you and the details are as follows -";
        if($modified){
            $subject = 'Notification of task schedule changed!';
            $bodyText = "A task schedule has been changed and the details are as follows -";            
        }
        $mailParams = array(
            'to' => $this->getEmail(),
            'from' => ' info@xtremeonecoder.com',
            'recipient' => $this->getTitle(),
            'sender' => 'Heating System',
            'subject' => $subject,
            'messagebody' => "
                Dear <strong>{$this->getTitle()}</strong>,

                {$bodyText}
                
                <strong>Ticket Reference:</strong> #{$ticket->getReference()}
                <strong>Ticket Title:</strong> {$ticket->getTitle()}
                <strong>Task Details:</strong> 
                {$ticket->getDescription()}
                    
                <strong>Customer Name:</strong> {$member->getTitle()}
                <strong>Phone Number:</strong> {$member->phone_no}
                <strong>Mobile Number:</strong> {$member->phone_no}
                <strong>Email Address:</strong> {$member->email}
                <strong>Scheduled Date:</strong> {$schedule->scheduled_date}    
                <strong>Starting Time:</strong> {$schedule->from_time}    
                <strong>Finishing Time:</strong> {$schedule->to_time}    
                <strong>Customer Address:</strong> 
                {$member->getAddress()}
                
                <strong>Task Allocator's Comment:</strong>
                {$schedule->getDescription()}
                
                Please be on due date and time and execute the task properly.
                
                Best Regards,
                Heating System
            ",
            //'messagebody' => nl2br(html_entity_decode(nl2br($this->getMessage()), ENT_QUOTES)),
            'mailtype' => 'html'
        );

        // sent mail
        $mail = Zend_Controller_Action_HelperBroker::getStaticHelper('Mail');
        $mail->send($mailParams);
    }

    public function cancelScheduleMember($technician = null, $ticket = null, $schedule = null, $modified = false){        
        if($technician == null || $ticket == null || $schedule == null){
            return null;
        }
        
        // dont send email if localhost
        if($_SERVER['HTTP_HOST'] == 'localhost'){return null;}
        
        // prepare mail params
        $view = new Zend_View();
        $subject = 'Notification of service schedule cancelled!';
        $bodyText = "Your support ticket service schedule has been cancelled and the details are as follows -";
//        if($modified){
//            $subject = 'Notification of service rescheduled!';
//            $bodyText = "Your support ticket has been rescheduled and the details are as follows -";            
//        }
        $mailParams = array(
            'to' => $this->getEmail(),
            'from' => ' info@xtremeonecoder.com',
            'recipient' => $this->getTitle(),
            'sender' => 'Heating System',
            'subject' => $subject,
            'messagebody' => "
                Dear <strong>{$this->getTitle()}</strong>,

                {$bodyText}
                
                <strong>Ticket Reference:</strong> #{$ticket->getReference()}
                <strong>Ticket Title:</strong> {$ticket->getTitle()}
                <strong>Technician Name:</strong> {$technician->getTitle()}
                <strong>Phone Number:</strong> {$technician->phone_no}
                <strong>Mobile Number:</strong> {$technician->phone_no}
                <strong>Email Address:</strong> {$technician->email}
                <strong>Scheduled Date:</strong> {$schedule->scheduled_date}    
                <strong>Starting Time:</strong> {$schedule->from_time}    
                <strong>Finishing Time:</strong> {$schedule->to_time}    
                
                We are sorry for this inconvenient. Your support ticket will be rescheduled soon.               

                Best Regards,
                Heating System
            ",
            //'messagebody' => nl2br(html_entity_decode(nl2br($this->getMessage()), ENT_QUOTES)),
            'mailtype' => 'html'
        );

        // sent mail
        $mail = Zend_Controller_Action_HelperBroker::getStaticHelper('Mail');
        $mail->send($mailParams);
    }
    
    public function cancelScheduleTechnician($member = null, $ticket = null, $schedule = null, $modified = false){        
        if($member == null || $ticket == null || $schedule == null){
            return null;
        }
        
        // dont send email if localhost
        if($_SERVER['HTTP_HOST'] == 'localhost'){return null;}
        
        // prepare mail params
        $view = new Zend_View();
        $subject = 'Notification of task schedule cancelled!';
        $bodyText = "A task schedule has been cancelled and the details are as follows -";
//        if($modified){
//            $subject = 'Notification of task schedule changed!';
//            $bodyText = "A task schedule has been changed and the details are as follows -";            
//        }
        $mailParams = array(
            'to' => $this->getEmail(),
            'from' => ' info@xtremeonecoder.com',
            'recipient' => $this->getTitle(),
            'sender' => 'Heating System',
            'subject' => $subject,
            'messagebody' => "
                Dear <strong>{$this->getTitle()}</strong>,

                {$bodyText}
                
                <strong>Ticket Reference:</strong> #{$ticket->getReference()}
                <strong>Ticket Title:</strong> {$ticket->getTitle()}
                <strong>Task Details:</strong> 
                {$ticket->getDescription()}
                    
                <strong>Customer Name:</strong> {$member->getTitle()}
                <strong>Phone Number:</strong> {$member->phone_no}
                <strong>Mobile Number:</strong> {$member->phone_no}
                <strong>Email Address:</strong> {$member->email}
                <strong>Scheduled Date:</strong> {$schedule->scheduled_date}    
                <strong>Starting Time:</strong> {$schedule->from_time}    
                <strong>Finishing Time:</strong> {$schedule->to_time}    
                <strong>Customer Address:</strong> 
                {$member->getAddress()}
                
                <strong>Task Allocator's Comment:</strong>
                {$schedule->getDescription()}                

                Please do not attempt to perform the task until you get further notification.
                
                Best Regards,
                Heating System
            ",
            //'messagebody' => nl2br(html_entity_decode(nl2br($this->getMessage()), ENT_QUOTES)),
            'mailtype' => 'html'
        );

        // sent mail
        $mail = Zend_Controller_Action_HelperBroker::getStaticHelper('Mail');
        $mail->send($mailParams);
    }

    public function notifyMemberTicketResolved($ticket = null){
        if($ticket == null){return null;}
        
        // dont send email if localhost
        if($_SERVER['HTTP_HOST'] == 'localhost'){return null;}
        
        // prepare mail params
        $view = new Zend_View();
        $subject = 'Notification of support ticket resolved!';
        $bodyText = "Your support ticket has been resolved and the details are as follows -";
        $mailParams = array(
            'to' => $this->getEmail(),
            'from' => ' info@xtremeonecoder.com',
            'recipient' => $this->getTitle(),
            'sender' => 'Heating System',
            'subject' => $subject,
            'messagebody' => "
                Dear <strong>{$this->getTitle()}</strong>,

                {$bodyText}
                
                <strong>Ticket Reference:</strong> #{$ticket->getReference()}
                <strong>Ticket Title:</strong> {$ticket->getTitle()}
                
                Thank you so much for using our service. Hope you would always be with us.
                
                Best Regards,
                Heating System
            ",
            //'messagebody' => nl2br(html_entity_decode(nl2br($this->getMessage()), ENT_QUOTES)),
            'mailtype' => 'html'
        );

        // sent mail
        $mail = Zend_Controller_Action_HelperBroker::getStaticHelper('Mail');
        $mail->send($mailParams);
    }

    public function notifyInvoiceToMember($ticket = null, $schedule = null, $invoice = null, $modified = false){
        if($ticket == null || $schedule == null || $invoice == null){
            return null;
        }
        
        // dont send email if localhost
        if($_SERVER['HTTP_HOST'] == 'localhost'){return null;}
        
        // prepare mail params
        $view = new Zend_View();
        $subject = 'Notification of service payment invoice!';
        $bodyText = "Your service payment invoice is created and the details are as follows -";
        if($modified){
            $subject = 'Reminder of service payment invoice!';
            $bodyText = "Your service payment invoice is not paid yet and the details are as follows -";
        }
        $mailParams = array(
            'to' => $this->getEmail(),
            'from' => ' info@xtremeonecoder.com',
            'recipient' => $this->getTitle(),
            'sender' => 'Heating System',
            'subject' => $subject,
            'messagebody' => "
                Dear <strong>{$this->getTitle()}</strong>,

                {$bodyText}
                
                <strong>Ticket Reference:</strong> #{$ticket->getReference()}
                <strong>Ticket Title:</strong> {$ticket->getTitle()}
                <strong>Technician Name:</strong> {$schedule->getTechnician()->getTitle()}                
                <strong>Scheduled Date:</strong> {$schedule->scheduled_date}    
                
                <h4>Payment Invoice</h4>
                -----------------------------------------------------------------
                
                <strong>Invoice Title:</strong> {$invoice->getTitle()}    
                <strong>Amount Payable:</strong> {$invoice->getAmount()}    
                <strong>Invoice Description:</strong> 
                {$invoice->getDescription()}    
                
                -----------------------------------------------------------------

                Thank you so much for using our service. Hope you would always be with us.
                
                Best Regards,
                Heating System
            ",
            //'messagebody' => nl2br(html_entity_decode(nl2br($this->getMessage()), ENT_QUOTES)),
            'mailtype' => 'html'
        );

        // sent mail
        $mail = Zend_Controller_Action_HelperBroker::getStaticHelper('Mail');
        $mail->send($mailParams);
    }
    
    public function sendResetPasswordNotification(){
        // dont send email if localhost
        if($_SERVER['HTTP_HOST'] == 'localhost'){return null;}
        
        // prepare mail params
        $view = new Zend_View();
        $loginLink = "http://{$_SERVER['HTTP_HOST']}{$view->url(array(), 'member_login', true)}";
        $mailParams = array(
            'to' => $this->getEmail(),
            'from' => ' info@xtremeonecoder.com',
            'recipient' => $this->getTitle(),
            'sender' => 'Heating System',
            'subject' => 'Password reset confirmation mail!',
            'messagebody' => "
                Dear {$this->getTitle()},

                You have successfully reset your account new password.

                New Login Details:
                Email: <strong>{$this->getEmail()}</strong>
                Password: ******* <strong>(Hidden for security)</strong>

                Now you can login your account using new password, please click on the link below -

                <a href='{$loginLink}'>{$loginLink}</a>

                Best Regards,
                Heating System
            ",
            //'messagebody' => nl2br(html_entity_decode(nl2br($this->getMessage()), ENT_QUOTES)),
            'mailtype' => 'html'
        );

        // sent mail
        $mail = Zend_Controller_Action_HelperBroker::getStaticHelper('Mail');
        $mail->send($mailParams);
    }
    
    public function sendActivateAccountNotification(){
        // dont send email if localhost
        if($_SERVER['HTTP_HOST'] == 'localhost'){return null;}
        
        // prepare mail params
        $view = new Zend_View();
        $loginLink = "http://{$_SERVER['HTTP_HOST']}{$view->url(array(), 'member_login', true)}";
        $mailParams = array(
            'to' => $this->getEmail(),
            'from' => ' info@xtremeonecoder.com',
            'recipient' => $this->getTitle(),
            'sender' => 'Heating System',
            'subject' => 'Account activation confirmation mail!',
            'messagebody' => "
                Dear {$this->getTitle()},

                You have successfully activated your account.

                Now you can login your account, please click on the link below -

                <a href='{$loginLink}'>{$loginLink}</a>

                Best Regards,
                Heating System
            ",
            //'messagebody' => nl2br(html_entity_decode(nl2br($this->getMessage()), ENT_QUOTES)),
            'mailtype' => 'html'
        );

        // sent mail
        $mail = Zend_Controller_Action_HelperBroker::getStaticHelper('Mail');
        $mail->send($mailParams);
    }
}
