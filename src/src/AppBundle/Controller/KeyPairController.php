<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Group;
use AppBundle\Entity\KeyPair;
use AppBundle\Entity\User;
use AppBundle\Form\GroupType;
use AppBundle\Form\KeyPairType;
use AppBundle\Form\UserType;
use Doctrine\ORM\EntityManager;
use phpseclib\Crypt\RSA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/key-pair")
 */
class KeyPairController extends Controller
{
    /**
     * @Route("/", name="keyPair_index")
     */
    public function indexAction()
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var KeyPair $keyPairs */
        $keyPairs = $em->getRepository('AppBundle:KeyPair')->findAll();

        return $this->render('key-pair/index.html.twig', [
            'keyPairs' => $keyPairs
        ]);
    }

    /**
     * @Route("/add", name="keyPair_add")
     */
    public function addAction(Request $request)
    {
        if ($request->get('cancel')) {
            return $this->redirectToRoute('keyPair_index');
        }

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $keyPair = new KeyPair();
        $form = $this->createForm(KeyPairType::class, $keyPair);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->addFlash('success', $this->get('translator')->trans('Key pair "%name%" successfully created.', ['name' => $keyPair->getName()]));

            $cryptRsa = new RSA();
            $cryptRsa->setPublicKeyFormat(RSA::PUBLIC_FORMAT_OPENSSH);

            $key = $cryptRsa->createKey(4096);

            $keyPair->setPrivateKey($key['privatekey']);
            $keyPair->setPublicKey($key['publickey']);

            $em->persist($keyPair);
            $em->flush();

            return $this->redirectToRoute('keyPair_index');
        }

        return $this->render('key-pair/form.html.twig', [
            'title' => 'Create key pair',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/edit/{id}", name="keyPair_edit")
     */
    public function editAction(Request $request, $id)
    {
        if ($request->get('cancel')) {
            return $this->redirectToRoute('keyPair_index');
        }

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var KeyPair $keyPair */
        $keyPair = $em->getRepository('AppBundle:KeyPair')->findOneBy(['id' => $id]);

        if (!$keyPair) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(KeyPairType::class, $keyPair);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->addFlash('success', $this->get('translator')->trans('Key pair "%name%" successfully updated.', ['name' => $keyPair->getName()]));

            $em->persist($keyPair);
            $em->flush();

            return $this->redirectToRoute('keyPair_index');
        }

        return $this->render('user/form.html.twig', [
            'title' => 'Edit key pair',
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/delete/{id}", name="keyPair_delete")
     */
    public function deleteAction(Request $request, $id)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var KeyPair $keyPair */
        $keyPair = $em->getRepository('AppBundle:KeyPair')->findOneBy(['id' => $id]);

        if (!$keyPair) {
            throw $this->createNotFoundException();
        }

        if ($request->isMethod('post')) {

            if (!$request->get('cancel')) {
                $em->remove($keyPair);
                $em->flush();

                $this->addFlash('success', $this->get('translator')->trans('Key pair "%name%" successfully deleted.', ['name' => $keyPair->getName()]));
            }

            return $this->redirectToRoute('keyPair_index');
        }

        return $this->render(
            'delete-form.html.twig',
            array(
                'headline' => $this->get('translator')->trans('Really delete key pair?'),
                'text' => $this->get('translator')->trans('Are you really sure you want to delete this key pair?'),
                'entityTitle' => $this->get('translator')->trans('Key pair name: %name%', ['name' => $keyPair->getName()])
            )
        );
    }

}
