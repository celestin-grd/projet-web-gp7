<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Validator.php';
require_once __DIR__ . '/../core/Datanormalizer.php';
use JulienLinard\Carousel\Carousel;
use JulienLinard\Carousel\CarouselItem;

/**
 * Contrôleur de gestion des statistiques
 *
 */
class StatistiquesController extends Controller
{
    public function show()
    {

        $statsModel = new Statistiques();
        $topWishlist = $statsModel->getTopWishlist();
        $topCandidatures = $statsModel->getTopCandidatures();
        $nbOffres = count($statsModel->getAllOffres());
        $nbCandidatures = count($statsModel->getAllPostulants());

        $txtWish = '';
        $txtCand = '';
        foreach ($topWishlist as $elem) {
            $txtWish .= $elem['nom'].' '.$elem['prenom'].' : '.$elem['nb']."\n";
        }
        foreach ($topCandidatures as $elem) {
            $txtCand .= $elem['nom'].' '.$elem['prenom'].' : '.$elem['nb']."\n";
        }
        $testimonials = [
            [
                'id'        => '1',
                'title'     => "Nombre d'Offres",
                'content'   => (string)($nbOffres),
                'image'     => 'https://static.web4all.local/app-icons/icon-192.png',
            ],
            [
                'id'        => '2',
                'title'     => "Nombre de Candidatures",
                'content'   => (string)($nbCandidatures),
                'image'     => 'https://static.web4all.local/app-icons/icon-192.png',
            ],
            [
                'id'        => '3',
                'title'     => 'Top Wishliste',
                'content'   => $txtWish,
                'image'     => 'https://static.web4all.local/app-icons/icon-192.png',
            ],
            [
                'id'        => '4',
                'title'     => 'Top Candidatures',
                'content'   => $txtCand,
                'image'     => 'https://static.web4all.local/app-icons/icon-192.png',
            ],
        ];






        // $testimonialCarousel = Carousel::testimonial('testimonial-carousel', $testimonials, [
        //     'transition' => 'fade',
        //     'autoplayInterval' => 5000,
        // ]);
        // return $this->render('statistiques/show', [
        //     'testimonials'      => $testimonialCarousel->render(),
        // ]);
        $carousel = new Carousel('custom', Carousel::TYPE_CARD);
        $carousel->addItem(new CarouselItem(
            id: 'item1',
            title: "Nombre d'Offres",
            content: (string)($nbOffres),
            image: STATIQUE . PREFIX . '/app-icons/icon-192.png',
            link: '',
            attributes: ['class' => 'kpi-multiline']
        ));
        $carousel->addItem(new CarouselItem(
            id: 'item2',
            title: "Nombre de Candidatures",
            content: (string)($nbCandidatures),
            image: STATIQUE . PREFIX . '/app-icons/icon-192.png',
            link: '',
            attributes: ['class' => 'kpi-multiline']
        ));
        $carousel->addItem(new CarouselItem(
            id: 'item3',
            title: 'Top Wish-list',
            content: (string)($txtWish),
            image: STATIQUE . PREFIX . '/app-icons/icon-192.png',
            link: '',
            attributes: ['class' => 'kpi-multiline']
        ));
        $carousel->addItem(new CarouselItem(
            id: 'item4',
            title: 'Top Candidatures',
            content: (string)($txtCand),
            image: STATIQUE . PREFIX . '/app-icons/icon-192.png',
            link: '',
            attributes: ['class' => 'kpi-multiline']
        ));


        return $this->render('statistiques/show', [
            'testimonials'      => $carousel->render(),
        ]);

    }


}
