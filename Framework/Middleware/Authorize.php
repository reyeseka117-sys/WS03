<?php
    namespace Framework\Middleware;

    use Framework\Session;
    use Framework\Authorization;

    class Authorize {
        /**
         * Handle middleware actions like 'auth' and 'guest'
         * @param string $middleware
         * @return void
         */
        public function handle($middleware) {
            Session::start();


            
            $user = Session::get('user');

            if ($middleware === 'auth') {
                if ($user === null) {
                    redirect('/login');
                }
            }

            if ($middleware === 'guest') {
                if ($user !== null) {
                    redirect('/');
                }
            }
        }
    }
?>