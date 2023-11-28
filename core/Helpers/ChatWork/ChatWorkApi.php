<?php

namespace Core\Helpers\ChatWork;

class ChatWorkApi
{
    // API Request endpoints
    const REQUEST_BASE = 'https://api.chatwork.com/v2';
    const ME_PATH = '/me';
    const MY_STATUS_PATH = '/my/status';
    const MY_TASKS_PATH = '/my/tasks';
    const CONTACTS_PATH = '/contacts';
    const ROOMS_PATH = '/rooms';

    // API parameters endpoints format
    const ROOM_DETAIL = '/rooms/%d';
    const ROOM_MEMBERS = '/rooms/%d/members';
    const ROOM_MESSAGES = '/rooms/%d/messages';
    const ROOM_MESSAGE_DETAIL = '/rooms/%d/messages/%d';
    const ROOM_TASKS = '/rooms/%d/tasks';
    const ROOM_TASK_DETAIL = '/rooms/%d/tasks/%d';
    const ROOM_FILES = '/rooms/%d/files';
    const ROOM_FILE_DETAIL = '/rooms/%d/files/%d';

    public $apiKey;

    /** @var ChatWorkConnection $connection */
    public $connection;

    /**
     * Constructor
     * @param string $apiKey
     */
    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
        $this->connection = new ChatWorkConnection($apiKey);
    }

    /**
     * Create request parameters syntax
     * @param array
     * @return ChatWorkParams
     */
    public function createParams($args)
    {
        return new ChatWorkParams($args);
    }

    /**
     * Get my information data
     *
     * @return object
     * @throws \Exception
     */
    public function getInfo()
    {
        $url = self::REQUEST_BASE . self::ME_PATH;
        $response = $this->connection->request('GET', $url);
        return $this->makeResponse($response);
    }

    /**
     * @return object
     * @throws \Exception
     */
    public function getStatus()
    {
        $url = self::REQUEST_BASE . self::MY_STATUS_PATH;
        $response = $this->connection->request('GET', $url);
        return $this->makeResponse($response);
    }

    /**
     * Get my tasks
     * @param ChatWorkParams $params
     * @return object
     * @throws \Exception
     */
    public function getTasks($params = null)
    {
        if (!($params instanceof ChatWorkParams)) {
            $params = new ChatWorkParams();
        }
        if (true !== ($valid = $params->isValidMyTaskRequest())) {
            throw new \Exception($valid);
        }

        $suffix = ($params->toURIParams() !== '') ? '?' . $params->toURIParams() : '';
        $url = self::REQUEST_BASE . self::MY_TASKS_PATH . $suffix;
        $response = $this->connection->request('GET', $url);

        return $this->makeResponse($response);
    }

    /**
     * @return object
     * @throws \Exception
     */
    public function getContacts()
    {
        $url = self::REQUEST_BASE . self::CONTACTS_PATH;
        $response = $this->connection->request('GET', $url);
        return $this->makeResponse($response);
    }

    /**
     * @return object
     * @throws \Exception
     */
    public function getRooms()
    {
        $url = self::REQUEST_BASE . self::ROOMS_PATH;
        $response = $this->connection->request('GET', $url);
        return $this->makeResponse($response);
    }

    /**
     * Get room detail info
     * @param ChatWorkParams $params
     * @return object
     * @throws \Exception
     */
    public function getRoomDetail(ChatWorkParams $params)
    {
        if (true !== ($valid = $params->isValidRoomID())) {
            throw new \Exception($valid);
        }

        $url = self::REQUEST_BASE . sprintf(self::ROOM_DETAIL, $params->room_id);
        $response = $this->connection->request('GET', $url);
        return $this->makeResponse($response);
    }

    /**
     * Create new chatroom
     * @param ChatWorkParams $params
     * @return object
     * @throws \Exception
     */
    public function createRoom(ChatWorkParams $params)
    {
        if (true !== ($valid = $params->isValidCreateRoomRequest())) {
            throw new \Exception($valid);
        }

        $url = self::REQUEST_BASE . self::ROOMS_PATH;
        $response = $this->connection->request('POST', $url, [], $params->toURIParams());
        return $this->makeResponse($response);
    }

    /**
     * Upadte room info
     * @param ChatWorkParams $params
     * @return object
     * @throws \Exception
     */
    public function updateRoom(ChatWorkParams $params)
    {
        if (true !== ($valid = $params->isValidUpdateRoomRequest())) {
            throw new \Exception($valid);
        }

        $url = self::REQUEST_BASE . sprintf(self::ROOM_DETAIL, $params->room_id);
        $response = $this->connection->request('PUT', $url, [], $params->toURIParams(['room_id']));
        return $this->makeResponse($response);
    }

    /**
     * Leave the room
     * @param ChatWorkParams $params
     * @return object
     * @throws \Exception
     */
    public function leaveRoom(ChatWorkParams $params)
    {
        if (true !== ($valid = $params->isValidRoomID())) {
            throw new \Exception($valid);
        }

        $params->action_type = 'leave';
        $url = self::REQUEST_BASE . sprintf(self::ROOM_DETAIL, $params->room_id);
        $response = $this->connection->request('DELETE', $url, [], $params->toURIParams());
        return $this->makeResponse($response);
    }

    /**
     * Delete room
     * @param ChatWorkParams $params
     * @return object
     * @throws \Exception
     */
    public function deleteRoom(ChatWorkParams $params)
    {
        if (true !== ($valid = $params->isValidRoomID())) {
            throw new \Exception($valid);
        }

        $params->action_type = 'delete';
        $url = self::REQUEST_BASE . sprintf(self::ROOM_DETAIL, $params->room_id);
        $response = $this->connection->request('DELETE', $url, [], $params->toURIParams());
        return $this->makeResponse($response);
    }

    /**
     * Get room members
     * @param ChatWorkParams $params
     * @return object
     * @throws \Exception
     */
    public function getRoomMembers(ChatWorkParams $params)
    {
        if (true !== ($valid = $params->isValidRoomID())) {
            throw new \Exception($valid);
        }

        $url = self::REQUEST_BASE . sprintf(self::ROOM_MEMBERS, $params->room_id);
        $response = $this->connection->request('GET', $url);
        return $this->makeResponse($response);
    }

    /**
     * Update room members
     * @param ChatWorkParams $params
     * @return object
     * @throws \Exception
     */
    public function updateRoomMembers(ChatWorkParams $params)
    {
        if (true !== ($valid = $params->isValidUpdateRoomMembers())) {
            throw new \Exception($valid);
        }

        $url = self::REQUEST_BASE . sprintf(self::ROOM_MEMBERS, $params->room_id);
        $response = $this->connection->request('PUT', $url, [], $params->toURIParams(['room_id']));
        return $this->makeResponse($response);
    }

    /**
     * Get room message posts
     * @param ChatWorkParams $params
     * @return object
     * @throws \Exception
     * @TODO implement
     */
    public function getRoomMessages(ChatWorkParams $params)
    {
        // not implemented at 2013/12/03
        throw new \Exception('Sorry, this API has not implemented.');
        /*
        if ( true !== ($valid = $params->isValidRoomID()) )
        {
            throw new ChatworkException($valid);
        }

        $response = $this->connection->request(
            'GET',
            self::REQUEST_BASE . sprintf(self::ROOM_MESSAGES, $params->room_id)
        );

        return $this->makeResponse($response);
        */
    }

    /**
     * Post mesage to room
     * @param ChatWorkParams $params
     * @return object
     * @throws \Exception
     */
    public function postRoomMessage(ChatWorkParams $params)
    {
        if (true !== ($valid = $params->isValidPostRoomMessage())) {
            throw new \Exception($valid);
        }

        $url = self::REQUEST_BASE . sprintf(self::ROOM_MESSAGES, $params->room_id);
        $response = $this->connection->request('POST', $url, [], $params->toURIParams(['room_id']));
        return $this->makeResponse($response);
    }

    /**
     * Get room message detail
     * @param ChatWorkParams $params
     * @return object
     * @throws \Exception
     */
    public function getRoomMessageDetail(ChatWorkParams $params)
    {
        if (true !== ($valid = $params->isValidGetRoomMessage())) {
            throw new \Exception($valid);
        }

        $url = self::REQUEST_BASE . sprintf(self::ROOM_MESSAGE_DETAIL, $params->room_id, $params->message_id);
        $response = $this->connection->request('GET', $url);
        return $this->makeResponse($response);
    }

    /**
     * Get room task list
     * @param ChatWorkParams $params
     * @return object
     * @throws \Exception
     */
    public function getRoomTasks(ChatWorkParams $params)
    {
        if (true !== ($valid = $params->isValidRoomID())) {
            throw new \Exception($valid);
        }

        $url = self::REQUEST_BASE . sprintf(self::ROOM_TASKS, $params->room_id) . '?' . $params->toURIParams(array('room_id'));
        $response = $this->connection->request('GET', $url);
        return $this->makeResponse($response);
    }

    /**
     * Add room task
     * @param ChatWorkParams $params
     * @return object
     * @throws \Exception
     */
    public function addRoomTask(ChatWorkParams $params)
    {
        if (true !== ($valid = $params->isValidAddRoomTask())) {
            throw new \Exception($valid);
        }

        $url = self::REQUEST_BASE . sprintf(self::ROOM_TASKS, $params->room_id);
        $response = $this->connection->request('POST', $url, [], $params->toURIParams(['room_id']));
        return $this->makeResponse($response);
    }

    /**
     * Get room task detail
     * @param ChatWorkParams $params
     * @return object
     * @throws \Exception
     */
    public function getRoomTaskDetail(ChatWorkParams $params)
    {
        if (true !== ($valid = $params->isValidRoomID()) || true !== ($valid = $params->isValidTaskID())) {
            throw new \Exception($valid);
        }

        $url = self::REQUEST_BASE . sprintf(self::ROOM_TASK_DETAIL, $params->room_id, $params->task_id);
        $response = $this->connection->request('GET', $url);
        return $this->makeResponse($response);
    }

    /**
     * Get room uploaded files info
     * @param ChatWorkParams $params
     * @return object
     * @throws \Exception
     */
    public function getRoomFiles(ChatWorkParams $params)
    {
        if (true !== ($valid = $params->isValidGetRoomFiles())) {
            throw new \Exception($valid);
        }

        $suffix = ($params->toURIParams() !== '') ? '?' . $params->toURIParams() : '';
        $url = self::REQUEST_BASE . sprintf(self::ROOM_FILES, $params->room_id) . $suffix;
        $response = $this->connection->request('GET', $url);
        return $this->makeResponse($response);
    }

    /**
     * Get room uploaded file detail
     * @param ChatWorkParams $params
     * @return object
     * @throws \Exception
     */
    public function getRoomFileDetail(ChatWorkParams $params)
    {
        if (true !== ($valid = $params->isValidGetRoomFileDetail())) {
            throw new \Exception($valid);
        }

        $url = self::REQUEST_BASE . sprintf(self::ROOM_FILES, $params->room_id);
        $response = $this->connection->request('GET', $url);
        return $this->makeResponse($response);
    }

    /**
     * Make/format API response
     * @access protected
     * @param object $response
     * @return object
     * @throws \Exception
     */
    protected function makeResponse($response)
    {
        $body = json_decode($response->body);

        if (preg_match('/^2[0-9]{2}$/', (string)$response->status)) {
            return $body;
        } else {
            throw new \Exception($body->errors[0]);
        }
    }
}
