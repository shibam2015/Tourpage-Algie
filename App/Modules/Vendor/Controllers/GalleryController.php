<?php

namespace Multiple\Vendor\Controllers;

/**
 * Index Controller
 * @author Algie
 */
class GalleryController extends VendorController {

    /**
     * Initializing controller
     */
    public function initialize() {
        parent::initialize();
        $this->initializeLayout();
    }

    /**
     * Index Acton for Index Controller
     */
    public function indexAction($action = 'list')
    {
        $this->tag->setTitle('Gallery');
        $gallerries = \Tourpage\Models\ToursReviewGallery::query();
        $this->view->gallery = $this->getGalleries($gallerries->execute());
        $this->view->members = $this->getMembers();
    }
    
    public function saveAction()
    {
        $galleryIds = isset($this->request->getPost()['galleryId']) ? $this->request->getPost()['galleryId'] : [];
        foreach ($galleryIds as $id) {
            $gallery = \Tourpage\Models\ToursReviewGallery::findFirstByGalleryId($id);
            // update gallery to true
            $gallery->isShown = 1;
            $gallery->save();
        }
        $this->response->redirect('/vendor/gallery');
    }

    private function getGalleries($result)
    {
        $url = $this->request->getServer('HTTP_POST');
        $data = [];
        if (!empty($result)) {
            foreach($result as $item) {
                $data['images']['image'][] = $url . $item->imagePath;
                $data['images']['date_uploaded'][] = date("F j, Y", strtotime($item->dateUploaded));
                $data['images']['memberId'][] = $item->memberId;
                $data['images']['galleryId'][] = $item->galleryId;
                $data['images']['isShown'][] = $item->isShown;
            }
        }
        return $data;
    }
    
    private function getMembers()
    {
        $data = [];
        $members = \Tourpage\Models\Members::query();
        $members->leftJoin('\Tourpage\Models\ToursReviewGallery', 'b.memberId = \Tourpage\Models\Members.memberId', 'b');
        $result = $members->execute();
        foreach($result as $member) {
            $data[$member->memberId] = "{$member->firstName} {$member->lastName}";
        }
        return $data;
    }
}
