<?php

namespace VoxSocioBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\View\TwitterBootstrap3View;

use VoxSocioBundle\Entity\Socio;

/**
 * Socio controller.
 *
 */
class SocioController extends Controller
{
    /**
     * Lists all Socio entities.
     *
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $queryBuilder = $em->getRepository('VoxSocioBundle:Socio')->createQueryBuilder('e');

        list($filterForm, $queryBuilder) = $this->filter($queryBuilder, $request);
        list($socios, $pagerHtml) = $this->paginator($queryBuilder, $request);
        
        $totalOfRecordsString = $this->getTotalOfRecordsString($queryBuilder, $request);

        return $this->render('socio/index.html.twig', array(
            'socios' => $socios,
            'pagerHtml' => $pagerHtml,
            'filterForm' => $filterForm->createView(),
            'totalOfRecordsString' => $totalOfRecordsString,

        ));
    }

    /**
    * Create filter form and process filter request.
    *
    */
    protected function filter($queryBuilder, Request $request)
    {
        $session = $request->getSession();
        $filterForm = $this->createForm('VoxSocioBundle\Form\SocioFilterType');

        // Reset filter
        if ($request->get('filter_action') == 'reset') {
            $session->remove('SocioControllerFilter');
        }

        // Filter action
        if ($request->get('filter_action') == 'filter') {
            // Bind values from the request
            $filterForm->handleRequest($request);

            if ($filterForm->isValid()) {
                // Build the query from the given form object
                $this->get('lexik_form_filter.query_builder_updater')->addFilterConditions($filterForm, $queryBuilder);
                // Save filter to session
                $filterData = $filterForm->getData();
                $session->set('SocioControllerFilter', $filterData);
            }
        } else {
            // Get filter from session
            if ($session->has('SocioControllerFilter')) {
                $filterData = $session->get('SocioControllerFilter');
                
                foreach ($filterData as $key => $filter) { //fix for entityFilterType that is loaded from session
                    if (is_object($filter)) {
                        $filterData[$key] = $queryBuilder->getEntityManager()->merge($filter);
                    }
                }
                
                $filterForm = $this->createForm('VoxSocioBundle\Form\SocioFilterType', $filterData);
                $this->get('lexik_form_filter.query_builder_updater')->addFilterConditions($filterForm, $queryBuilder);
            }
        }

        return array($filterForm, $queryBuilder);
    }


    /**
    * Get results from paginator and get paginator view.
    *
    */
    protected function paginator($queryBuilder, Request $request)
    {
        //sorting
        $sortCol = $queryBuilder->getRootAlias().'.'.$request->get('pcg_sort_col', 'id');
        $queryBuilder->orderBy($sortCol, $request->get('pcg_sort_order', 'desc'));
        // Paginator
        $adapter = new DoctrineORMAdapter($queryBuilder);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage($request->get('pcg_show' , 10));

        try {
            $pagerfanta->setCurrentPage($request->get('pcg_page', 1));
        } catch (\Pagerfanta\Exception\OutOfRangeCurrentPageException $ex) {
            $pagerfanta->setCurrentPage(1);
        }
        
        $entities = $pagerfanta->getCurrentPageResults();

        // Paginator - route generator
        $me = $this;
        $routeGenerator = function($page) use ($me, $request)
        {
            $requestParams = $request->query->all();
            $requestParams['pcg_page'] = $page;
            return $me->generateUrl('socio', $requestParams);
        };

        // Paginator - view
        $view = new TwitterBootstrap3View();
        $pagerHtml = $view->render($pagerfanta, $routeGenerator, array(
            'proximity' => 3,
            'prev_message' => 'previous',
            'next_message' => 'next',
        ));

        return array($entities, $pagerHtml);
    }
    
    
    
    /*
     * Calculates the total of records string
     */
    protected function getTotalOfRecordsString($queryBuilder, $request) {
        $totalOfRecords = $queryBuilder->select('COUNT(e.id)')->getQuery()->getSingleScalarResult();
        $show = $request->get('pcg_show', 10);
        $page = $request->get('pcg_page', 1);

        $startRecord = ($show * ($page - 1)) + 1;
        $endRecord = $show * $page;

        if ($endRecord > $totalOfRecords) {
            $endRecord = $totalOfRecords;
        }
        return "Showing $startRecord - $endRecord of $totalOfRecords Records.";
    }
    
    

    /**
     * Displays a form to create a new Socio entity.
     *
     */
    public function newAction(Request $request)
    {
    
        $socio = new Socio();
        $form   = $this->createForm('VoxSocioBundle\Form\SocioType', $socio);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($socio);
            $em->flush();
            
            $editLink = $this->generateUrl('socio_edit', array('id' => $socio->getId()));
            $this->get('session')->getFlashBag()->add('success', "<a href='$editLink'>New socio was created successfully.</a>" );
            
            $nextAction=  $request->get('submit') == 'save' ? 'socio' : 'socio_new';
            return $this->redirectToRoute($nextAction);
        }
        return $this->render('socio/new.html.twig', array(
            'socio' => $socio,
            'form'   => $form->createView(),
        ));
    }
    

    /**
     * Finds and displays a Socio entity.
     *
     */
    public function showAction(Socio $socio)
    {
        $deleteForm = $this->createDeleteForm($socio);
        return $this->render('socio/show.html.twig', array(
            'socio' => $socio,
            'delete_form' => $deleteForm->createView(),
        ));
    }
    
    

    /**
     * Displays a form to edit an existing Socio entity.
     *
     */
    public function editAction(Request $request, Socio $socio)
    {
        $deleteForm = $this->createDeleteForm($socio);
        $editForm = $this->createForm('VoxSocioBundle\Form\SocioType', $socio);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($socio);
            $em->flush();
            
            $this->get('session')->getFlashBag()->add('success', 'Edited Successfully!');
            return $this->redirectToRoute('socio_edit', array('id' => $socio->getId()));
        }
        return $this->render('socio/edit.html.twig', array(
            'socio' => $socio,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    
    

    /**
     * Deletes a Socio entity.
     *
     */
    public function deleteAction(Request $request, Socio $socio)
    {
    
        $form = $this->createDeleteForm($socio);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($socio);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'The Socio was deleted successfully');
        } else {
            $this->get('session')->getFlashBag()->add('error', 'Problem with deletion of the Socio');
        }
        
        return $this->redirectToRoute('socio');
    }
    
    /**
     * Creates a form to delete a Socio entity.
     *
     * @param Socio $socio The Socio entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Socio $socio)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('socio_delete', array('id' => $socio->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
    
    /**
     * Delete Socio by id
     *
     */
    public function deleteByIdAction(Socio $socio){
        $em = $this->getDoctrine()->getManager();
        
        try {
            $em->remove($socio);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'The Socio was deleted successfully');
        } catch (Exception $ex) {
            $this->get('session')->getFlashBag()->add('error', 'Problem with deletion of the Socio');
        }

        return $this->redirect($this->generateUrl('socio'));

    }
    

    /**
    * Bulk Action
    */
    public function bulkAction(Request $request)
    {
        $ids = $request->get("ids", array());
        $action = $request->get("bulk_action", "delete");

        if ($action == "delete") {
            try {
                $em = $this->getDoctrine()->getManager();
                $repository = $em->getRepository('VoxSocioBundle:Socio');

                foreach ($ids as $id) {
                    $socio = $repository->find($id);
                    $em->remove($socio);
                    $em->flush();
                }

                $this->get('session')->getFlashBag()->add('success', 'socios was deleted successfully!');

            } catch (Exception $ex) {
                $this->get('session')->getFlashBag()->add('error', 'Problem with deletion of the socios ');
            }
        }

        return $this->redirect($this->generateUrl('socio'));
    }
    

}
