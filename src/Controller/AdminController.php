<?php
/**
 * Created by PhpStorm.
 * User: T2G-WEB
 * Date: 18/04/2022
 * Time: 10:45
 */

namespace App\Controller;


use App\Entity\Config;
use App\Service\ResponsableService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{

    /** @var EntityManagerInterface $em */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/admin", name="admin")
     * @param ResponsableService $responsableService
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function admin(ResponsableService $responsableService)
    {
        $responsables = $responsableService->getResponsables();

        $config = $this->getConfig();

        return $this->render('admin.html.twig', [
            "responsables" => $responsables,
            "config" => $config,
        ]);
    }


    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/block_subscription", name="block_subscription")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function blockSubscription()
    {
        $config = $this->getConfig();

        $config->setIsSubscriptionOpen(false);

        $this->em->flush();

        $this->addFlash("success", "Les inscriptions sont bloquées");
        return $this->redirectToRoute('admin');
    }


    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/activate_subscription", name="activate_subscription")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function activateSubscription()
    {
        $config = $this->getConfig();

        $config->setIsSubscriptionOpen(true);

        $this->em->flush();

        $this->addFlash("success", "Les inscriptions sont activées");
        return $this->redirectToRoute('admin');
    }


    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/participant_number", name="participant_number", methods={"post"})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function participantNumber(Request $request)
    {
        $participantnumber = $request->request->get('participantnumber');

        if ($participantnumber < 1) {
            $this->addFlash("error", "Le nombre de bénévoles par jour ne peut pas être plus petit que 1");
            return $this->redirectToRoute('admin');
        }

        $config = $this->getConfig();
        $config->setMaxParticipantNumber($participantnumber);

        $this->em->flush();

        $this->addFlash("success", "Nombre de bénévoles par jour modifié");
        return $this->redirectToRoute('admin');
    }


    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/info_message", name="info_message", methods={"post"})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function infoMessage(Request $request)
    {
        $message = $request->request->get('message');
        $color = $request->request->get('messagecolor');

        $messageLength = strlen($message);
        if ($messageLength > 255) {
            $this->addFlash("error", "Le message ne peut pas faire plus de 255 caractères. Actuellement il en fait $messageLength. Merci de le raccourcir.");
            return $this->redirectToRoute('admin');
        }

        if (empty($message)) {
            $this->addFlash("error", "Vous ne pouvez pas renseigner un message vide");
            return $this->redirectToRoute('admin');
        }

        if (empty($color)) {
            $this->addFlash("error", "Merci de sélectionner une couleur");
            return $this->redirectToRoute('admin');
        }

        $config = $this->getConfig();

        $config->setMessageContent($message);
        $config->setMessageColor($color);

        $this->em->flush();

        $this->addFlash("success", "Message d'information mis à jour");
        return $this->redirectToRoute('admin');
    }


    /**
     * @Route("/update-responsable/{day}", name="dayForm", methods={"post"})
     * @IsGranted("ROLE_ADMIN")
     * @param Request $request
     * @param ResponsableService $responsableService
     * @param $day
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function dayForm(Request $request, ResponsableService $responsableService, $day)
    {
        $name = $request->request->get('name');
        $email = $request->request->get('email');
        $phone = $request->request->get('phone');

        $responsableService->saveResponsable($day, $name, $email, $phone);
        $this->addFlash("success", "Modification effectuée");

        return $this->redirectToRoute('admin');
    }

    private function getConfig()
    {
        $configs = $this->em->getRepository(Config::class)->findAll();

        if (empty($configs)) {
            $config = new Config();
            $this->em->persist($config);
        } else {
            $config = array_pop($configs);
        }
        return $config;
    }
}