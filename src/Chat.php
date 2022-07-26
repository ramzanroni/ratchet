<?php

namespace MyApp;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

require "../db/users.php";
require "../db/chatrooms.php";

class Chat implements MessageComponentInterface
{
    protected $clients;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
        echo "server started";
    }

    public function onOpen(ConnectionInterface $conn)
    {
        // Store the new connection to send messages to later
        $this->clients->attach($conn);

        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $numRecv = count($this->clients) - 1;
        echo sprintf(
            'Connection %d sending message "%s" to %d other connection%s' . "\n",
            $from->resourceId,
            $msg,
            $numRecv,
            $numRecv == 1 ? '' : 's'
        );
        $data = json_decode($msg, true);
        $objUser = new \users;
        $objChatroom = new \chatrooms;
        $objChatroom->setUserId($data['userId']);
        $objChatroom->setMsg($data['msg']);
        $objChatroom->setCreatedOn(date('Y-m-d h:i:s'));
        if ($objChatroom->saveChatRoom()) {
            $objUser->setId($data['userId']);
            $user = $objUser->getUserById();
            $info['from'] = $user['name'];
            $info['msg'] = $data['msg'];
            $info['dt'] = date('d-m-Y h:i:s');
        }

        foreach ($this->clients as $client) {
            if ($from == $client) {
                $info['from'] = "Me";
            } else {
                $info['from'] = $user['name'];
            }
            // if ($from !== $client) { //close this condition for both
            // The sender is not the receiver, send to each client connected
            $client->send(json_encode($info));
            // }
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }
}
