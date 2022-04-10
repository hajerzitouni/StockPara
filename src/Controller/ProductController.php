<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Historique;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Shuchkin\SimpleXLSX;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * @Route("/product")
 */
class ProductController extends AbstractController
{
    /**
     * @Route("/", name="app_product_index", methods={"GET"})
     */
    public function index(ProductRepository $productRepository): Response
    {
        return $this->render('product/index.html.twig', [
            'products' => $productRepository->findAll(),
        ]);
    }
    
    /**
     * @Route("/list", name="app_vente", methods={"GET"})
     */
    public function list(ProductRepository $productRepository): Response
    {
        return $this->render('product/list.html.twig', [
            'products' => $productRepository->findAll(),
        ]);
    }

      /**
   * @Route("/upload-excel", name="upload-excel" , methods={"GET", "POST"})
    */

public function xslx(Request $request)
{
   
    $em = $this->getDoctrine()->getManager();
   $file= __DIR__ . '/../../public/uploads/stock.xlsx';  //choose the folder in which the uploaded file will be stored
  
   if ( $xlsx = SimpleXLSX::parse($file) ) {
 
    foreach( $xlsx->rows() as $key=> $r ) {

         $product = new Product();
          
            $product->setCode($r[0]);
            $product->setQtt($r[1]);
            $product->setPrix($r[2]);
            $em->persist($product); 
            
              // here Doctrine checks all the fields of all fetched data and make a transaction to the database.
          
            }
            $em->flush();
 
    } else {
        echo SimpleXLSX::parseError();
    }
     return $this->redirectToRoute('app_product_index');
}

    /**
     * @Route("/new", name="app_product_new", methods={"GET", "POST"})
     */
    public function new(Request $request, ProductRepository $productRepository): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $productRepository->add($product);
            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('product/new.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_product_show", methods={"GET"})
     */
    public function show(Product $product): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="app_product_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Product $product, ProductRepository $productRepository): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $productRepository->add($product);
            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('product/edit.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_product_delete", methods={"POST"})
     */
    public function delete(Request $request, Product $product, ProductRepository $productRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->request->get('_token'))) {
            $productRepository->remove($product);
        }

        return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
    }

  
/**
     * @Route("/vendu/{id}", name="product-vendu", methods={"GET", "POST"})
     */
    public function vendu($id)
    {
        $em=$this->getDoctrine()->getManager();
       // $panier=$em->getRepository(Panier::class)->find($idp);
        $prd=$em->getRepository(Product::class)->find($id);
        $prd->setQtt($prd->getQtt()-1);
        $em->persist($prd);
        $historique = new Historique();
        $historique->setProduit($prd);
        $historique->setDate(new \DateTime());
        $em->persist($historique);
        $em->flush();
        return $this->redirectToRoute('app_vente');
    
    }


    
}
