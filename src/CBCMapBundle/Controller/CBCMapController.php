<?php

namespace CBCMapBundle\Controller;

use CBCMapBundle\Entity\CBC;
use CBCMapBundle\Entity\Picture;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Ivory\GoogleMap\Map;
use Ivory\GoogleMap\Base\Coordinate;
use Ivory\GoogleMap\Overlay\Marker;
use Ivory\GoogleMap\Overlay\Animation;
use Ivory\GoogleMap\Overlay\Icon;
use Ivory\GoogleMap\Overlay\MarkerShape;
use Ivory\GoogleMap\Overlay\MarkerShapeType;
use Ivory\GoogleMap\Overlay\Symbol;
use Ivory\GoogleMap\Overlay\SymbolPath;
use Ivory\GoogleMap\Helper\Builder\ApiHelperBuilder;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;
use Ivory\GoogleMapBundle\Form\Type\PlaceAutocompleteType;

class CBCMapController extends Controller
{
    /**
     * @Route("/", name="cbc_map")
     */
    public function mapAction()
    {
        $map = new Map();
        $map->setAutoZoom(false);
        $map->setHtmlId('map_canvas');
        $map->setCenter(new Coordinate(45.7569751,4.8531652,17));
        $map->setMapOption('zoom', 12);
        $map->setMapOption('width', '600px');
        $map->setStylesheetOption('width', '600px');
        $map->setStylesheetOption('height', '400px');

        $cbcs = $this->getDoctrine()
                      ->getRepository('CBCMapBundle:CBC')
                      ->findAll();

        $icon_urls = array('cat' =>'http://maps.google.com/mapfiles/ms/icons/blue-dot.png',
                           'beer' => 'http://maps.google.com/mapfiles/ms/icons/yellow-dot.png',
                           'cheesecake' => 'http://maps.google.com/mapfiles/ms/icons/purple-dot.png');

        foreach ($cbcs as $cbc) {
            $icon = new Icon();
            $icon->setUrl($icon_urls[$cbc->getCategory()]);
            $symbol = new Symbol(SymbolPath::CIRCLE);
            $marker = new Marker(
                $cbc->getCoordinatesAsCoordinate(),
                null,
                $icon,
                $symbol,
                new MarkerShape(MarkerShapeType::CIRCLE, [1.1, 2.1, 1.4]),
                array('clickable' => true)
            );
            $map->getOverlayManager()->addMarker($marker);
        }

        return $this->render('CBCMapBundle:CBCMap:index.html.twig', array('map' => $map));
    }

    /**
     * @Route("/upload", name="cbc_upload")
     */
    public function uploadAction(Request $request)
    {
        $apiHelperBuilder = ApiHelperBuilder::create();
        $apiHelper = $apiHelperBuilder->build();
        $form = $this->createFormBuilder()
                     ->add('name', TextType::class, array(
                         'attr' => array(
                             'class' => 'form-control',
                             'style' => 'margin-bottom:15px'
                            )))
                     ->add('category', ChoiceType::class, array(
                         'choices' => array(
                             'Cat' => 'cat', 'Beer' => 'beer', 'Cheesecake' => 'cheesecake'
                            ),
                            'attr' => array(
                                'class' => 'form-control',
                                'style' => 'margin-bottom:15px'
                            )))
                     ->add('address', PlaceAutocompleteType::class, array(
                         'attr' => array(
                             'class' => 'form-control',
                             'style' => 'margin-bottom:15px'
                            )))
                     ->add('picture', FileType::class, array(
                         'required' => false,
                         'label' => 'Upload picture',
                         'attr' => array(
                             'class' => 'form-control',
                             'style' => 'margin-bottom:15px; border-color:white; padding:0'

                         ),
                         'constraints' => array(
                             new File(
                                 array(
                                     'maxSize' => '5M',
                                     'mimeTypes' => array(
                                         'image/jpeg',
                                         'image/jpg',
                                         'image/png'
                                     ),
                                     'mimeTypesMessage' => 'Please upload a valid jpg or png file'
                                   )
                                )
                            )))
                     ->add('save', SubmitType::class, array(
                         'attr' => array(
                             'class' => 'btn btn-primary',
                             'style' => 'margin-bottom:15px'
                            )))
                     ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $name = $form['name']->getData();
            $category = $form['category']->getData();
            $address = $form['address']->getData();
            $coordinates = '0,0';
            $picture = $form['picture']->getData();
            $pictures_available = $picture != null;
            $author_id = 1;
            
            $cbc = new CBC();
            $cbc->setName($name);
            $cbc->setCategory($category);
            $cbc->setAddress($address);
            $cbc->setCoordinates($coordinates);
            $cbc->setPicturesAvailable($pictures_available);
            $cbc->setAuthorId($author_id);

            $em = $this->getDoctrine()->getManager();
            $em->persist($cbc);
            $em->flush();

            if ($pictures_available) {
                $cbc_id = $cbc->getId();
                $pic = new Picture();
                $pic->savePicture($cbc_id, $picture, $author_id, $name);
                $em->persist($pic);
                $em->flush();
            }

            $this->addFlash(
                'notice',
                'CBC Added'
            );

            return $this->redirectToRoute('cbc_list', array('category' => $category));
        }

        return $this->render('CBCMapBundle:CBCMap:upload.html.twig', array('form' => $form->createView()));
    }

    /**
     * @Route("/list/", name="cbc_list")
     */
    public function listAction(Request $request)
    {
        $elems = $this->getDoctrine()
                      ->getRepository('CBCMapBundle:CBC')
                      ->findAll();
        $forms = array();
        $del_forms = array();
        $em = $this->getDoctrine()->getManager();
        
        foreach ($elems as $elem) {
            $cbc_id = $elem->getId();
            $form = $this->get('form.factory')->createNamed('form_' . $cbc_id)
                        ->add('picture', FileType::class, array(
                            'label' => 'Upload picture',
                            'attr' => array(
                                'class' => 'form-control',
                                'style' => 'margin-bottom:15px; border-color:white; padding:0'

                            ),
                            'constraints' => array(
                                new File(
                                    array(
                                        'maxSize' => '5M',
                                        'mimeTypes' => array(
                                            'image/jpeg',
                                            'image/png'
                                        ),
                                        'mimeTypesMessage' => 'Please upload a valid jpg or png file'
                                    )
                                    )
                                )))
                        ->add('save', SubmitType::class, array(
                            'attr' => array(
                                'class' => 'btn btn-primary',
                                'style' => 'margin-bottom:15px'
                            )));
            
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $picture = $form['picture']->getData();
                $name = $elem->getName();
                $author_id = 1;
                $pic = new Picture();
                $pic->savePicture($cbc_id, $picture, $author_id, $name);
                
                $cbc = $em->getRepository('CBCMapBundle\Entity\CBC')
                        ->findBy(array('id' => $cbc_id));
                $cbc->setPicturesAvailable(true);

                $em->persist($pic);
                $em->persist($cbc);
                $em->flush();
            }
            
            $forms[$cbc_id] = $form->createView();    
            
            $del_form = $this->get('form.factory')
                        ->createNamed('del_form_' . $cbc_id)
                        ->add('delete', SubmitType::class, array(
                            'attr' => array(
                                'label' => 'Delete',
                                'class' => 'btn btn-danger',
                                'style' => 'margin:10px'
                            )));
            
            $del_form->handleRequest($request);

            if ($del_form->isSubmitted() && $del_form->isValid()) {
                
                $cbc = $em->getRepository('CBCMapBundle\Entity\CBC')
                        ->findBy(array('id' => $cbc_id))[0];
                $pics = $em->getRepository('CBCMapBundle\Entity\Picture')
                        ->findBy(array('cBCId' => $cbc_id));

                foreach ($pics as $pic) {
                    unlink($pic->getPath());
                    $em->remove($pic);
                }

                $em->remove($cbc);
                $em->flush();
                unset($del_forms[$cbc_id]);
                unset($forms[$cbc_id]);
                $new_elems = $this->getDoctrine()
                      ->getRepository('CBCMapBundle:CBC')
                      ->findAll();
                return $this->render('CBCMapBundle:CBCMap:list.html.twig', array('elems' => $new_elems, 'forms' => $forms, 'del_forms' => $del_forms));
            }
            $del_forms[$cbc_id] = $del_form->createView();    
        }
        return $this->render('CBCMapBundle:CBCMap:list.html.twig', array('elems' => $elems, 'forms' => $forms, 'del_forms' => $del_forms));
    }
}
