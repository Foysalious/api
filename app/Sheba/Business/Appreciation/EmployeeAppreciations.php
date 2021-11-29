<?php namespace App\Sheba\Business\Appreciation;

use App\Models\BusinessMember;
use Carbon\Carbon;
use Sheba\Dal\BusinessMemberBadge\BusinessMemberBadgeRepository;

class EmployeeAppreciations
{
    const LATE_LATEEF = 'late_lateef';
    const EARLY_BIRD = 'early_bird';

    /*** @var BusinessMemberBadgeRepository $businessMemberBadgeRepo*/
    private $businessMemberBadgeRepo;

    public function __construct(BusinessMemberBadgeRepository $business_member_badge_repo)
    {
        $this->businessMemberBadgeRepo = $business_member_badge_repo;
    }

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
            $giver = [];
            foreach ($stickers as $sticker) {
                $giver[] = $this->getEmployeeInfo($sticker['giver_id'])['name'];
            }
            $sticker = $stickers->first();
            array_push($grouped_stickers, [
                'id' => $sticker['id'],
                'image' => $sticker['image'],
                'appreciation_givers' => $giver,
                'number_of_stickers' => $stickers->count(),
            ]);
        }
        $early_bird_badge = $this->businessMemberBadgeRepo->where('business_member_id', $business_member->id)->where('badge', self::EARLY_BIRD);
        $late_lateef_badge = $this->businessMemberBadgeRepo->where('business_member_id', $business_member->id)->where('badge', self::LATE_LATEEF);
        if ($early_bird_badge->count()) {
            array_push($grouped_stickers, [
            'id' => rand(-9999,-1),
            'image' => "https://cdn-shebaxyz.s3.ap-south-1.amazonaws.com/b2b/stickers/sharp_cookiee_stickers-3.png",
            'appreciation_givers' => null,
            'number_of_stickers' => $early_bird_badge->count()
        ]);
        array_push($all_complements, [
            "id" => rand(-9999,-1),
            "complement" => "Thanks for your extra effort! keep up the pace.",
            "sticker" => [
                "id" => rand(-9999,-1),
                "image" => "https://cdn-shebaxyz.s3.ap-south-1.amazonaws.com/b2b/stickers/thank_you_stickers.png"
            ],
            "given_by" => 'Admin',
            "date" => $early_bird_badge->last()->created_at->format('dS F')
        ]);
        }
        if ($late_lateef_badge->count()) {
            array_push($grouped_stickers, [
                'id' => rand(-9999,-1),
                'image' => "https://cdn-shebaxyz.s3.ap-south-1.amazonaws.com/b2b/stickers/sharp_cookiee_stickers-3.png",
                'appreciation_givers' => null,
                'number_of_stickers' => $late_lateef_badge->count()
            ]);
            array_push($all_complements, [
                "id" => rand(-9999,-1),
                "complement" => "Sometimes it’s considerable. Make sure you don’t make it a habit!",
                "sticker" => [
                    "id" => rand(-9999,-1),
                    "image" => "https://cdn-shebaxyz.s3.ap-south-1.amazonaws.com/b2b/stickers/thank_you_stickers.png"
                ],
                "given_by" => 'Admin',
                "date" => $late_lateef_badge->last()->created_at->format('dS F')
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