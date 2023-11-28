<?php

namespace Core\Helpers\ChatWork;

class ChatWork
{
    /** @var null|string Chatwork API. */
    protected $chatworkApi;

    /** @var null|string Chatwork room */
    protected $roomError;


    public function __construct()
    {
        $config = config('services.chat_work');

        $this->chatworkApi = new ChatWorkApi($config['api_token']);
        $this->roomError = $config['room_id_error'];
    }

    /**
     * http://developer.chatwork.com/vi/endpoint_rooms.html#POST-rooms-room_id-messages
     *
     * @param null $message
     * @param null $roomId
     * @return bool
     * @throws \Exception
     */
    public function writeMessage($message = null, $roomId = null)
    {
        if (empty($roomId)) {
            $roomId = $this->roomError;
        }

        $param = [
            'room_id' => $roomId,
            'body' => $message,
        ];

        $objParam = $this->chatworkApi->createParams($param);
        $result = (array)$this->chatworkApi->postRoomMessage($objParam);

        if (empty($result['message_id'])) {
            return false;
        }

        return true;
    }
}
