<?php namespace App\Sheba\Business\Appreciation;

use App\Models\BusinessMember;

class EmployeeAppreciations
{
    /**
     * @param BusinessMember $business_member
     * @return array[]
     */
    public function getEmployeeAppreciations(BusinessMember $business_member)
    {
        $employee_appreciations = $business_member->appreciations()->with('sticker')->orderBy('id', 'DESC')->get();

        $all_stickers = [];
        $all_complements = [];
        foreach ($employee_appreciations as $appreciation) {
            $sticker = $appreciation->sticker;
            array_push($all_stickers, [
                'id' => $sticker->id,
                'giver_id' => $appreciation->giver_id,
                'image' => $sticker->image,
            ]);
            if ($appreciation->note) {
                array_push($all_complements, [
                    'id' => $appreciation->id,
                    'complement' => $appreciation->note,
                    'sticker' => [
                        'id' => $sticker->id,
                        'image' => $sticker->image,
                    ],
                    'given_by' => $this->getEmployeeInfo($appreciation->giver_id),
                    'date' => $appreciation->created_at->format('dS F')
                ]);
            }
        }

        $group_stickers = collect($all_stickers)->groupBy('id');
        $grouped_stickers = [];
        foreach ($group_stickers as $stickers) {
           /* $giver = [];
            foreach ($stickers as $sticker) {
                $giver[] = $this->getEmployeeInfo($sticker['giver_id'])['name'];
            }*/
            $sticker = $stickers->first();
            array_push($grouped_stickers, [
                'id' => $sticker['id'],
                'image' => $sticker['image'],
                #'appreciation_givers' => $giver,
                'number_of_stickers' => $stickers->count(),
            ]);
        }

        return ['stickers' => $grouped_stickers, 'complements' => $all_complements];
    }

    /**
     * @param BusinessMember $business_member
     * @return array
     */
    public function getEmployeeStickers(BusinessMember $business_member)
    {
        $employee_appreciations = $business_member->appreciations()->with('sticker')->orderBy('id', 'DESC')->get();

        $all_stickers = [];
        foreach ($employee_appreciations as $appreciation) {
            $sticker = $appreciation->sticker;
            #$all_stickers[] = $sticker->image;
            array_push($all_stickers, [
                'id' => $sticker->id,
                'image' => $sticker->image
            ]);
        }

        return $all_stickers;
    }

    /**
     * @param $business_member_id
     * @return array
     */
    private function getEmployeeInfo($business_member_id)
    {
        $business_member = BusinessMember::find((int)$business_member_id);
        $member = $business_member->member;
        $profile = $member->profile;
        return [
            'name' => $profile->name ?: 'n/s'
        ];
    }
}