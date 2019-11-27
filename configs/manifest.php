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

return array(
    'landing_home' => array(
        'route' => '/:action/*',
        'defaults' => array(
            'module' => 'default',
            'controller' => 'index',
            'action' => 'index'
        ),
        'reqs' => array(
            'controller' => '\D+',
            'action' => '\D+',
        )
    ),
    'member_login' => array(
        'route' => 'login/*',
        'defaults' => array(
            'module' => 'default',
            'controller' => 'auth',
            'action' => 'login'
        ),
        'reqs' => array(
            'action' => '\D+',
            'controller' => '\D+'
        )
    ),
    'member_logout' => array(
        'route' => 'logout/*',
        'defaults' => array(
            'module' => 'default',
            'controller' => 'auth',
            'action' => 'logout'
        ),
        'reqs' => array(
            'action' => '\D+',
            'controller' => '\D+'
        )
    ),
    'member_signup' => array(
        'route' => 'register/*',
        'defaults' => array(
            'module' => 'default',
            'controller' => 'auth',
            'action' => 'register'
        ),
        'reqs' => array(
            'action' => '\D+',
            'controller' => '\D+'
        )
    ),
    'forgot_password' => array(
        'route' => 'forgot/*',
        'defaults' => array(
            'module' => 'default',
            'controller' => 'auth',
            'action' => 'forgot'
        ),
        'reqs' => array(
            'action' => '\D+',
            'controller' => '\D+'
        )
    ),
    'account_settings' => array(
        'route' => 'account/:action/*',
        'defaults' => array(
            'module' => 'default',
            'controller' => 'user-settings',
            'action' => 'index'
        ),
        'reqs' => array(
            'controller' => '\D+',
            'action' => '(index|change-password)'
        )
    ),
    'account_activation' => array(
        'route' => 'activation/*',
        'defaults' => array(
            'module' => 'default',
            'controller' => 'auth',
            'action' => 'activation'
        ),
        'reqs' => array(
            'action' => '\D+',
            'controller' => '\D+'
        )
    ),
    'reset_password' => array(
        'route' => 'reset/:key/*',
        'defaults' => array(
            'module' => 'default',
            'controller' => 'auth',
            'action' => 'reset'
        ),
        'reqs' => array(
            'action' => '\D+',
            'controller' => '\D+'
        )
    ),
    'activate_account' => array(
        'route' => 'activate/:key/*',
        'defaults' => array(
            'module' => 'default',
            'controller' => 'auth',
            'action' => 'activate'
        ),
        'reqs' => array(
            'action' => '\D+',
            'controller' => '\D+'
        )
    ),
    'admin_dashboard' => array(
        'route' => 'admin/:action/*',
        'defaults' => array(
            'module' => 'default',
            'controller' => 'admin',
            'action' => 'index'
        ),
        'reqs' => array(
            'action' => '\D+',
            'controller' => '\D+'
        )
    ),
    'user_dashboard' => array(
        'route' => 'member/dashboard/*',
        'defaults' => array(
            'module' => 'default',
            'controller' => 'user',
            'action' => 'index'
        ),
        'reqs' => array(
            'action' => '\D+',
            'controller' => '\D+'
        )
    ),
    'member_profile' => array(
        'route' => 'member/:id/profile/*',
        'defaults' => array(
            'module' => 'default',
            'controller' => 'user',
            'action' => 'profile'
        ),
        'reqs' => array(
            'id' => '\d+',
            'action' => '\D+',
            'controller' => '\D+'
        )
    ),
    'my_profile' => array(
        'route' => 'my/profile/*',
        'defaults' => array(
            'module' => 'default',
            'controller' => 'user',
            'action' => 'my-profile'
        ),
        'reqs' => array(
            'action' => '\D+',
            'controller' => '\D+'
        )
    ),
    'user_tickets' => array(
        'route' => 'tickets/:action/*',
        'defaults' => array(
            'module' => 'default',
            'controller' => 'ticket',
            'action' => 'index'
        ),
        'reqs' => array(
            'controller' => '\D+',
            'action' => '(index|create|details|delete|trash|restore|reopen|close|priority|payment)'
        )
    ),
    'admin_tickets' => array(
        'route' => 'admin/tickets/:action/*',
        'defaults' => array(
            'module' => 'default',
            'controller' => 'admin-ticket',
            'action' => 'index'
        ),
        'reqs' => array(
            'controller' => '\D+',
            'action' => '(index|details|delete|trash|restore|reopen|close|resolved)'
        )
    ),
    'user_invoices' => array(
        'route' => 'invoices/:action/*',
        'defaults' => array(
            'module' => 'default',
            'controller' => 'invoice',
            'action' => 'index'
        ),
        'reqs' => array(
            'controller' => '\D+',
            'action' => '(index|details|payment)'
        )
    ),
    'admin_invoices' => array(
        'route' => 'admin/invoices/:action/*',
        'defaults' => array(
            'module' => 'default',
            'controller' => 'admin-invoice',
            'action' => 'index'
        ),
        'reqs' => array(
            'controller' => '\D+',
            'action' => '(index|create|edit|details|delete|restore|trash)'
        )
    ),
    'admin_tasks' => array(
        'route' => 'admin/schedules/:action/*',
        'defaults' => array(
            'module' => 'default',
            'controller' => 'admin-task',
            'action' => 'index'
        ),
        'reqs' => array(
            'controller' => '\D+',
            'action' => '(index|create|edit|delete|close|trash)'
        )
    ),
    'technician_tasks' => array(
        'route' => 'technician/schedules/:action/*',
        'defaults' => array(
            'module' => 'default',
            'controller' => 'technician-task',
            'action' => 'index'
        ),
        'reqs' => array(
            'controller' => '\D+',
            'action' => '(index|details|trash)'
        )
    ),
    'admin_users' => array(
        'route' => 'admin/users/:action/*',
        'defaults' => array(
            'module' => 'default',
            'controller' => 'admin-user',
            'action' => 'index'
        ),
        'reqs' => array(
            'controller' => '\D+',
            'action' => '(index|details|activate|inactivate|inactive|technician|make-technician|delete-technician)'
        )
    ),
    'admin_page_general' => array(
        'route' => 'admin/pages/:action/*',
        'defaults' => array(
            'module' => 'default',
            'controller' => 'admin-pages',
            'action' => 'browse'
        ),
        'reqs' => array(
            'action' => '\D+',
            'controller' => '\D+',
        )
    ),
    'admin_page_action' => array(
        'route' => 'admin/page/:id/:action/*',
        'defaults' => array(
            'module' => 'default',
            'controller' => 'admin-pages',
            'action' => ''
        ),
        'reqs' => array(
            'id' => '\d+',
            'controller' => '\D+',
            'action' => '(edit|delete)',
        )
    ),
    'admin_mail_general' => array(
        'route' => 'admin/mails/:action/*',
        'defaults' => array(
            'module' => 'default',
            'controller' => 'admin-mail',
            'action' => 'browse'
        ),
        'reqs' => array(
            'action' => '\D+',
            'controller' => '\D+'
        )
    ),
    'admin_mail_action' => array(
        'route' => 'admin/mail/:id/:action/*',
        'defaults' => array(
            'module' => 'default',
            'controller' => 'admin-mail',
            'action' => ''
        ),
        'reqs' => array(
            'id' => '\d+',
            'controller' => '\D+',
            'action' => '(edit|status|delete|test-mail|reset)',
        )
    ),
);
?>
