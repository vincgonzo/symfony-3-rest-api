<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Article;
use AppBundle\Exception\ResourceValidationException;
use AppBundle\Representation\Articles;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use FOS\RestBundle\Controller\FOSRestController;

use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Doctrine\ORM\EntityManager as Manager;

class ArticleController extends FOSRestController
{
    /**
     * @Rest\Get("/articles/search/", name="app_article_search")
     * @Rest\QueryParam(
     *     name="keyword",
     *     requirements="[a-zA-Z0-9]",
     *     nullable=true,
     *     description="The keyword to search for."
     * )
     * @Rest\QueryParam(
     *     name="order",
     *     requirements="asc|desc",
     *     default="asc",
     *     description="Sort order (asc or desc)"
     * )
     * @Rest\QueryParam(
     *     name="limit",
     *     requirements="\d+",
     *     default="15",
     *     description="Max number of movies per page."
     * )
     * @Rest\QueryParam(
     *     name="offset",
     *     requirements="\d+",
     *     default="0",
     *     description="The pagination offset"
     * )
     * @Rest\View()
     */
    public function searchAction(ParamFetcherInterface $paramFetcher)
    {
        $pager = $this->getDoctrine()->getRepository('AppBundle:Article')->search(
            $paramFetcher->get('keyword'),
            $paramFetcher->get('order'),
            $paramFetcher->get('limit'),
            $paramFetcher->get('offset')
        );

        return new Articles($pager);
    }

    /**
     * @Rest\Get("/articles", name="app_article_list")
     * @Rest\View
     */
    public function listAction()
    {
        $articles = $this->getDoctrine()->getRepository('AppBundle:Article')->findAll();

        return $articles;
    }

    /**
     * @Rest\Get(
     *     path = "/articles/{id}",
     *     name = "app_article_show",
     *     requirements = {"id"="\d+"}
     * )
     * @Rest\View
     */
    public function showAction(Article $article)
    {
        return $article;
    }

    /**
     * @Rest\Post("/articles")
     * @Rest\View(StatusCode = 201)
     * @ParamConverter("article", converter="fos_rest.request_body")
     */
    public function createAction(Article $article, ConstraintViolationList $violations)
    {
        if (count($violations)) {
            $message = 'The JSON sent contains invalid data. Here are the errors you need to correct: ';
            foreach ($violations as $violation) {
                $message .= sprintf("Field %s: %s ", $violation->getPropertyPath(), $violation->getMessage());
            }

            throw new ResourceValidationException($message);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($article);
        $em->flush();

        return $article;
    }


    /**
     * @Rest\Post("/article/update/")
     * @Rest\View()
     * @ParamConverter("article", converter="fos_rest.request_body")
     */
    public function updateAction(Article $article, ConstraintViolationList $violations)
    {
        // TODO What about the author ???
        // Validation
        $em = $this->getDoctrine()->getManager();
        $articleUpdate = $this->getDoctrine()->getRepository('AppBundle:Article')->find($article->getId());

        $articleUpdate->setContent($article->getContent());
        $articleUpdate->setTitle($article->getTitle());
        $em->persist($articleUpdate);
        $em->flush();

        return $articleUpdate;
    }

    /**
     * @Rest\Post("/article/delete/")
     * @Rest\View()
     * @ParamConverter("article", converter="fos_rest.request_body")
     */
    public function removeAction(Article $article)
    {
        $em = $this->getDoctrine()->getManager();
        $articleRemove = $this->getDoctrine()->getRepository('AppBundle:Article')->find($article->getId());

        if($articleRemove instanceof Article){
            $em->remove($articleRemove);
            $em->flush();

            return 'Article id : ' . $article->getId() . ' successfully removed';
        }
        return 'Article id : ' . $article->getId() . ' - doesn\'t exist or already removed.';
    }
}
