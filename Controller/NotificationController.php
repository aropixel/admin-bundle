<?php

namespace Aropixel\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Aropixel\AdminBundle\Entity\Notification;


/**
 * Notification controller.
 *
 * @Route("/notification")
 */
class NotificationController extends Controller
{

    /**
     * Surveille l'apparition de nouvelles notifications
     *
     * @Route("/get", name="notifications_get", options={"expose"=true}, methods={"GET"})
     */
    public function getNotificationsAction(Request $request)
    {
        $engine = $this->container->get('templating');
        $notifications = $this->get('admin.notifier')->getNotifications();

        $content = "";
        foreach ($notifications as $notification) {

            $avatar = false;
            $user = $notification->getUser();
            if ($user) {
                $avatar = $user->getImage();
            }
            $content.= $engine->render('AropixelAdminBundle:Notification:notification.html.twig', array('notification' => $notification, 'avatar' => $avatar));

        }

        $response = new Response($content);
        return $response;

    }

    /**
     * Marque les notifications commes vues
     *
     * @Route("/view", name="notifications_view", options={"expose"=true}, methods={"POST"})
     */
    public function viewNotificationsAction(Request $request)
    {

        $notifications = $this->get('admin.notifier')->viewNotifications();

        $response = new Response('');
        return $response;

    }

}
