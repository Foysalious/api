<?php namespace Sheba\CmDashboard;

use Sheba\Dal\Category\Category;
use App\Models\Customer;
use App\Models\Job;
use App\Models\Order;
use App\Models\Review;
use App\Sheba\Queries\SbuDashboard\CustomerBasedOnOrder;
use App\Sheba\Queries\SbuDashboard\MovieTicketSummary;
use App\Sheba\Queries\SbuDashboard\PartnerSummary;
use App\Sheba\Queries\SbuDashboard\TopUpSummary;
use App\Sheba\Queries\SbuDashboard\TrasnportTicketSummary;

class DashboardOnePageSummary
{
    public static $rr_duration = 30;

    /**
     * @return array
     */
    public function get()
    {
        list($data, $orders, $customers, $partners, $topUpSummary, $movieTicketSummary, $transportTicketSummary) = $this->_makeQuery();
        $data = $this->_calculateChildrenData($data);
        return ['sbu' => $data, 'orders' => $orders, 'customers' => $customers,
            'partners' => $partners, 'topup' => $topUpSummary, 'movie_ticket' => $movieTicketSummary,
            'transport_ticket' => $transportTicketSummary];
    }

    /**
     * @return mixed
     */
    private function _makeQuery()
    {
        $category_data = Category::parents()->published()
            ->with(['children' => function ($q) {
                $q->with(['orderCount', 'servedOrder', 'scheduledOrder', 'ratings'])->select(['parent_id', 'id']);
            }])->select('name', 'parent_id', 'id', 'icon', 'icon_png')->get();
        $orders = $this->_makeOrderSummary();
        $customers = $this->_makeCustomerSummary();
        $partners = $this->_makePartnerSummary();
        $topUpSummary = $this->_makeTopUpData();
        $movieTicketSummary = $this->_makeMovieTicketData();
        $transportTicketSummary = $this->_makeTransportTicketData();
        return [$category_data, $orders, $customers, $partners, $topUpSummary, $movieTicketSummary, $transportTicketSummary];
    }

    private function _makeOrderSummary()
    {
        $data['order_count'] = $this->_resolveCount(Order::orderedToday()->get()->toArray());
        $data['scheduled_count'] = $this->_resolveCount(Job::scheduledToday()->get()->toArray());
        $data['served_count'] = $this->_resolveCount(Order::servedToday()->get()->toArray());
        $data['c_sat_avg'] = round($this->_resolveCount(Review::todaysAvg()->get()->toArray()), 1);
        return $data;
    }

    private function _makeCustomerSummary()
    {
        $customerBasedOnOrder = new CustomerBasedOnOrder();
        $data['unique_today'] = $this->_resolveCount(Order::uniqueCustomerOrderToday()->get()->toArray());
        $data['registered_today'] = $this->_resolveCount(Customer::registeredToday()->get()->toArray());
        $data['new_user'] = $this->_resolveCount($customerBasedOnOrder->newUser());
        $data['returning_user'] = $this->_resolveCount($customerBasedOnOrder->returningUser());
        return $data;
    }

    private function _makePartnerSummary()
    {
        $partnerSummary = new PartnerSummary();
        $partners['verified_count'] = $this->_resolveCount($partnerSummary->verifiedToday());
        $partners['on_boarded_count'] = $this->_resolveCount($partnerSummary->onBoardToday());
        $partners['active_count'] = $this->_resolveCount($partnerSummary->active());
        $partners['wallet_amount'] = $this->_resolveCount($partnerSummary->totalWalletRecharge());
        return $partners;
    }

    private function _makeTopUpData()
    {
        $topUpSummary = new TopUpSummary();
        list($data['topup_count'], $data['topup_amount']) = $topUpSummary->todayAmount();
        $data['operator_summary'] = $topUpSummary->operatorSummary();
        return $data;
    }

    private function _makeMovieTicketData()
    {
        $movieTicketSummary = new MovieTicketSummary();
        list($data['movie_ticket_count'], $data['movie_ticket_amount']) = $movieTicketSummary->todayAmount();
        $data['operator_summary'] = $movieTicketSummary->operatorSummary();
        return $data;
    }

    private function _makeTransportTicketData()
    {
        $transportTicketSummary = new TrasnportTicketSummary();
        list($data['transport_ticket_count'], $data['transport_ticket_amount']) = $transportTicketSummary->todayAmount();
        $data['operator_summary'] = $transportTicketSummary->operatorSummary();
        return $data;
    }

    private function _resolveCount($data)
    {
        try {
            if (isset($data[0]) && !empty($data[0])) {
                return is_array($data[0]) ? ($data[0]['total'] != null ? $data[0]['total'] : 0) : ($data[0]->total != null ? $data[0]->total : 0);
            } else {
                return 0;
            }
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * @param $data
     * @return array
     */
    private function _calculateChildrenData($data)
    {
        $output = [];
        foreach ($data as $key => $category) {
            $output[$key] = ['name' => $category['name'], 'id' => $category['id'], 'icon' => $category['icon'], 'icon_png' => $category['icon_png']];
            list($order_count, $served_count, $scheduled_count, $cSet) = $this->_countChildrenData($category);
            $output[$key]['order_count'] = ['today' => $order_count];
            $output[$key]['served_order'] = ['today' => $served_count];
            $output[$key]['scheduled_order'] = ['today' => $scheduled_count];
            $output[$key]['c_sat'] = ['today' => round($cSet, 1)];
        }
        return $output;
    }

    /**
     * @param Category $category
     * @return array
     */
    private function _countChildrenData(Category $category)
    {
        $order_count =
        $served_count =
        $scheduled_count =
        $cSet_count =
        $cSet_total = 0;
        foreach ($category->children as $children) {
            $order_count += $children->orderCount->count();
            $served_count += $children->servedOrder->count();
            $scheduled_count += $children->scheduledOrder->count();
            list($count, $total) = $this->_ratingCount($children->ratings);
            $cSet_count += $count;
            $cSet_total += $total;
        }
        $cSet = $cSet_count > 0 ? $cSet_total / $cSet_count : 0;
        return [$order_count, $served_count, $scheduled_count, $cSet];
    }

    /**
     * @param $ratings
     * @return array
     */
    private function _ratingCount($ratings)
    {
        $count = 0;
        $total = 0;
        foreach ($ratings as $rating) {
            $count += $rating->review ? 1 : 0;
            $total += $rating->review ? $rating->review->rating : 0;
        }
        return [$count, $total];
    }
}
