<?php namespace Sheba\Repositories;

use Illuminate\Http\Request;
use Sheba\Repositories\Interfaces\ProfileNidRepositoryInterface;

class ProfileNidRepository extends BaseRepository implements ProfileNidRepositoryInterface
{
    public function saveImage(Request $request)
    {
        $data = [];
        if ($request->hasFile('nid_image')) {
            $data['nid_image'] = $this->_saveNIdImage($request);
        }
        return $data;
    }

    private function _saveNIdImage(Request $request)
    {
        list($avatar, $avatar_filename) = $this->makeThumb($request->file('profile_image'), $request->name);
        return $this->saveImageToCDN($avatar, getResourceAvatarFolder(), $avatar_filename);
    }

}
