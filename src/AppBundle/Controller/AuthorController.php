<?php

namespace AppBundle\Controller;

    use AppBundle\Entity\Author;
    use AppBundle\Exception\ResourceValidationException;
    use FOS\RestBundle\Controller\Annotations as Rest;
    use FOS\RestBundle\Request\ParamFetcherInterface;
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
    use FOS\RestBundle\Controller\FOSRestController;
    use Symfony\Component\Validator\ConstraintViolationList;
    use Symfony\Component\HttpFoundation\Response;

class AuthorController extends FOSRestController
{

    /**
     * @Rest\Get("/authors", name="app_author_list")
     * @Rest\View
     */
    public function listAction()
    {
        $authors = $this->getDoctrine()->getRepository('AppBundle:Author')->findAll();

        return $authors;
    }

    /**
     * @Rest\Get(
     *     path = "/authors/{id}",
     *     name = "app_author_show",
     *     requirements = {"id"="\d+"}
     * )
     * @Rest\View
     */
    public function showAction(Author $author)
    {
        return $author;
    }

    /**
     * @Rest\Post("/authors")
     * @Rest\View(StatusCode = 201)
     * @ParamConverter(
     *     "author",
     *     converter="fos_rest.request_body",
     *     options= {
     *          "validator" = { "groups"="Create" }
     *     }
     * )
     */
    public function createAction(Author $author, ConstraintViolationList $violations)
    {
        if (count($violations)) {
            $message = 'The JSON sent contains invalid data. Here are the errors you need to correct: ';
            foreach ($violations as $violation) {
                $message .= sprintf("Field %s: %s ", $violation->getPropertyPath(), $violation->getMessage());
            }

            throw new ResourceValidationException($message);
        }

        $em = $this->getDoctrine()->getManager();

        $em->persist($author);
        $em->flush();

        return $author;
    }

}