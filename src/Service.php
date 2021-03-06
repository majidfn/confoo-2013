<?php

namespace ConFoo\Mess {

    class Service implements API {

        /**
         * @var UserRepository
         */
        private $userRepository;

        /**
         * @param UserRepository $repository
         */
        public function __construct(UserRepository $repository) {
            $this->userRepository = $repository;
        }

        /**
         * Creates a new user account and
         * returns a unique authentication token
         *
         * @param string $username
         * @param string $email
         *
         * @throws UsernameUnavailableException
         * @throws InvalidUsernameException
         * @throws InvalidEmailException
         *
         * @return string Token
         */
        public function registerUser($username, $email) {
            if (strlen($username) < 4) {
                throw new InvalidUsernameException("'$username' is too short");
            }
            $token = new Token($username);

            try {
                $this->userRepository->saveUser(new User($username, $email, $token));
                return $token;
            } catch (UserRepositoryException $e) {
                throw new \RuntimeException('...', 0, $e);
            }

        }

        /**
         * @param Token $token
         * @param string $recipient Username
         * @param string $message
         *
         * @throws InvalidTokenException
         * @throws NotAuthorizedException
         * @throws InvalidUsernameException
         * @throws InvalidMessageException
         *
         * @return null
         */
        public function postMessage(Token $token, $recipient, $message) {
            $sender = $this->userRepository->findUserByToken($token);
            $recipientUser = $this->userRepository->findUserByUsername($recipient);
            $recipientUser->addMessage(new Message($sender, $message));
        }

        /**
         * @param Token $token
         *
         * @return array of Message
         */
        public function retrieveMessages(Token $token) {
            $user = $this->userRepository->findUserByToken($token);
            return $user->getMessages();
        }

    }

}
