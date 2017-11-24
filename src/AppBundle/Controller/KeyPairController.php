<?php

namespace AppBundle\Controller;

use AppBundle\Entity\KeyPair;
use AppBundle\Form\KeyPairType;
use Doctrine\ORM\EntityManagerInterface;
use phpseclib\Crypt\RSA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\Translator;

/**
 * @Route("/key-pair")
 */
class KeyPairController extends Controller
{
    /**
     * @Route("/", name="keyPair_index")
     */
    public function indexAction(EntityManagerInterface $entityManager)
    {
        /** @var KeyPair[] $keyPairs */
        $keyPairs = $entityManager->getRepository(KeyPair::class)->findAll();

        return $this->render('key-pair/index.html.twig', [
            'keyPairs' => $keyPairs
        ]);
    }

    /**
     * @Route("/add", name="keyPair_add")
     */
    public function addAction(Request $request, EntityManagerInterface $entityManager)
    {
        if ($request->get('cancel')) {
            return $this->redirectToRoute('keyPair_index');
        }

        $keyPair = new KeyPair();
        $form = $this->createForm(KeyPairType::class, $keyPair);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->addFlash('success', 'Key pair successfully created.');

            $cryptRsa = new RSA();
            $cryptRsa->setPublicKeyFormat(RSA::PUBLIC_FORMAT_OPENSSH);

            $key = $cryptRsa->createKey(4096);

            $keyPair->setPrivateKey($key['privatekey']);
            $keyPair->setPublicKey($key['publickey']);

            $entityManager->persist($keyPair);
            $entityManager->flush();

            return $this->redirectToRoute('keyPair_index');
        }

        return $this->render('key-pair/form.html.twig', [
            'title' => 'Create key pair',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="keyPair_edit")
     */
    public function editAction(Request $request, EntityManagerInterface $entityManager, $id)
    {
        if ($request->get('cancel')) {
            return $this->redirectToRoute('keyPair_index');
        }

        /** @var KeyPair $keyPair */
        $keyPair = $entityManager->getRepository(KeyPair::class)->findOneBy(['id' => $id]);

        if (!$keyPair) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(KeyPairType::class, $keyPair);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->addFlash('success', 'Key pair successfully updated.');

            $entityManager->flush();

            return $this->redirectToRoute('keyPair_index');
        }

        return $this->render('key-pair/form.html.twig', [
            'title' => 'Edit key pair',
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/delete", name="keyPair_delete")
     */
    public function deleteAction(Request $request, EntityManagerInterface $entityManager, Translator $translator, $id)
    {
        /** @var KeyPair $keyPair */
        $keyPair = $entityManager->getRepository(KeyPair::class)->findOneBy(['id' => $id]);

        if (!$keyPair) {
            throw $this->createNotFoundException();
        }

        if ($request->isMethod('post')) {
            if (!$request->get('cancel')) {
                $entityManager->remove($keyPair);
                $entityManager->flush();

                $this->addFlash('success', 'Key pair "%name%" successfully deleted.');
            }

            return $this->redirectToRoute('keyPair_index');
        }

        return $this->render(
            'delete-form.html.twig',
            array(
                'headline' => $translator->trans('Really delete key pair?'),
                'text' => $translator->trans('Are you really sure you want to delete this key pair?'),
                'entityTitle' => $translator->trans(
                    'Key pair name: %name%',
                    ['%name%' => $keyPair->getName()]
                )
            )
        );
    }

}
