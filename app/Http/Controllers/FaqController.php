<?php namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function getAffiliateFaqs(Request $request)
    {
        try {
            $faqs = array(
                array('group_label_bn' => 'সেবা বন্ধু', 'group_label_en' => 'Sheba Bondhu', 'group_name' => 'sheba_bondhu',
                    'questions' => array(
                        array('question_bn' => 'বন্ধু কী?', 'answer_bn' => 'বন্ধু হচ্ছে সেবা প্ল্যাটফর্ম লিমিটেডর, এমন একটি অ্যাপ যার মাধ্যমে আপনি আপনার বন্ধুকে হেল্প করার পাশাপাশি সহজে  বন্ধু পয়েন্ট আয় করতে পারবেন। বন্ধুতে আপনি সার্ভিস রেফার, টপ আপ, মুভি টিকেট, পরিবহন টিকেট বিক্রি করার মাধ্যমে বন্ধু পয়েন্ট আয় করতে পারবেন। উপার্জিত বন্ধু পয়েন্ট  দিয়ে পুনরায় টপ আপ, মুভি টিকেট, পরিবহন টিকেট কিনতে পারবেন। তবে বন্ধু পয়েন্ট ক্যাশ আউট করতে পারবেন না। ', 'question_en' => 'What is SHEBA Bondhu APP?', 'answer_en' => 'Bondhu, Sheba Platform Limited, an app that allows you to help your friends as well as earn Bondhu Points. You can earn Bondhu Points through the service referrals of Sheba.xyz or top ups to your friends. Using your earned Bondhu Point you can buy mobile recharge, movie and transportation ticket. However, Bondhu Point cannot be cash out.'),
                        array('question_bn' => 'আমার একাউন্ট ভেরিফাইড হয়নি কেন?', 'answer_bn' => 'আপনার একাউন্ট টি ভেরিফাইড করতে আপনাকে অবশই আপনার নিজের জাতীয় পরিচয় পত্রের ছবি এবং সেলফি ছবি প্রদান করতে হবে। বন্ধু প্রোফাইল ভেরিফাইড হতে ৭২ ঘন্টা সময় লাগবে, আপনাকে ম্যাসেজ এর মাধ্যমে জানানো হবে। তার পরও যদি আপানার বন্ধু প্রোফাইলটি ভেরিফাইড না হয় তবে ১৬৫১৬ এ যোগাযোগ করুন। বন্ধু প্রোফাইল ভেরিফাইড না হলে আপনি বন্ধু এপ এর কোনো সার্ভিস রেফার, টপ আপ, মুভি টিকেট, পরিবহন টিকেট বিক্রয়  করতে পারবেন না।', 'question_en' => 'Why my account is not verified yet?', 'answer_en' => 'To verify your Bondhu account you must submit your own NID photo and selfie photo from the "My Profile" side menu. It might take upto 72 hours to be verified your Bondhu profile and you\'ll be notified by SMS once your account gets verified.  If you don\'t get any update within 72 hours of your own NID submission, please call at 16516. Without Verified Bondhu account you\'ll not be able to do anything in the Bondhu app.'),)
                ),
                array('group_label_bn' => 'সার্ভিস রেফার কিভাবে কাজ করে?', 'group_label_en' => 'How does Service Refer work', 'group_name' => 'service_refer',
                    'questions' => array(
                        array('question_bn' => 'সার্ভিস রেফার কিভাবে কাজ করে?', 'answer_bn' => 'আপনার বন্ধুর যদি কোন সার্ভিস প্রয়োজন হয় আপনি তার জন্য বন্ধু অ্যাপ এর মাধ্যমে sheba.xyz এর সাভির্স রেফার করতে পারবেন। আপনার রেফারকৃত বন্ধুকে sheba.xyz হেল্প ডেস্ক হতে দ্রুত কল করা হবে এবং তার সার্ভিসটি প্রদান এর জন্য sheba.xyz একটি অর্ডার প্লেস করা হবে। একজন sheba.xyz ভেরিফাইড সার্ভিস প্রোভাইডার আপনার বন্ধুর নিকট পৌঁছে যাবে।', 'question_en' => 'How does service referral work?  ', 'answer_en' => 'You can refer service for your Bondhu App in sheba.xyz by this APP. Within a short time, your referred friend will receive a call from Sheba Help Desk and an order will placed. Sheba verified service provider will go to your friend\'s home to give his service. '),
                        array('question_bn' => 'সার্ভিস রেফার এর মাধ্যমে কিভাবে বন্ধু পয়েন্ট আয় করবেন?', 'answer_bn' => 'বন্ধু অ্যাপ এর মাধ্যমে সেবা তে আপনি আপনার পরিচিত জনের জন্য সার্ভিস রেফার করতে পারবেন। আগ্রহী গ্রাহকের নাম, ফোন নাম্বার ও কোন সার্ভিসটি লাগবে এই তথ্য গুল দিয়ে রেফার করুন। প্রতিটি রেফার সফল ভাবে সম্পন্ন হতেই আপনার বন্ধু  পয়েন্ট এ সার্ভিস মূল্যের ৫% বোনাস পেয়ে যাবেন।', 'question_en' => 'How to earn bondhu point by referring service for your friend? ', 'answer_en' => 'You can refer a service to your friend via Bondhu APP by giving information about Customer Name, Phone and Needed Service name you can refer your friend. On every successful refer you will get 5% of order amount as bonus in your Bondhu Point.'),)
                ),
                array('group_label_bn' => 'বন্ধু পয়েন্ট কি?', 'group_label_en' => 'What is Bondhu Point?', 'group_name' => 'money_bag',
                    'questions' => array(
                        array('question_bn' => 'বন্ধু পয়েন্ট কি?', 'answer_bn' => 'আপনি বন্ধু অ্যাপ এর মাধ্যমে যে সকল লেনদেন করবেন তার বিবরণ পাবেন  বন্ধু পয়েন্ট এর বিবরণী পেজে। এছাড়াও আপনি আপনার উপার্জিত বন্ধু পয়েন্ট দিয়ে টপ আপ এবং মুভি/পরিবহন টিকেট কিনতে পারবেন।', 'question_en' => 'What is Bondhu Point? ', 'answer_en' => 'The amount in Bondhu Point represent how much you have earned, spend through this app.You can use this Bondu Point for TOP-UP, Moive/Transport ticket purchase.'),)
                ),
                array('group_label_bn' => 'একাউন্ট স্থগিত হবার কারণ কী?', 'group_label_en' => 'THE REASON FOR SUSPENSION OF ACCOUNTS', 'group_name' => 'account_suspension',
                    'questions' => array(
                        array('question_bn' => 'আমার একাউন্ট স্থগিত হবার কারন কি কি?', 'answer_bn' => 'sheba.xyz সার্ভিস রেফার এর ক্ষেত্রে আপনি যদি ১০ বার ভুল অথবা মিথ্যা রেফার করেন তবে আপনার একাউন্টটি স্থগিত করা হবে। আপনি দিনে ২০ টির বেশি সার্ভিস রেফার করতে পারবেন না। এছাড়াও আপনি যদি কোনো প্রতারণামুলক কাজ করেন তাহলে আপনার একাউন্টটি বন্ধ হয়ে যেতে পারে এবং আপনার বিরুদ্ধে আইনত ব্যবস্থা নেওয়ার ক্ষমতা রাখে সেবা প্ল্যাটফর্ম লিমিটেড।', 'question_en' => 'What are the reasons behind my suspension ?  ', 'answer_en' => 'Your account will be suspended, if you give 10 fake or invalid reference or order. Your referral limit is 20 service(s) per day. If you do any illegal activity in the app, then Sheba Platform Limited holds the right to suspend your Bondhu account and take legal action aginst you.'),
                        array('question_bn' => 'আমার একাউন্টটি কতদিন স্থগিত থাকবে?', 'answer_bn' => 'আপনার একাউন্টটি সর্বোচ্চ ৩ দিন স্থগিত থাকতে পারে। তবে নিরাপত্তা জনিত কারণে আপনার একাউন্টটি স্থায়ী ভাবে আপনার বন্ধু একাউন্টটি বন্ধ করে দেওয়া হতে পারে। বিস্তারিত জানতে ১৬৫১৬ এ কল দিতে পারেন।', 'question_en' => 'How long my account will be suspended ?', 'answer_en' => 'Suspension will go away after 3 days automatically. But your account can be suspended permanently due to security concerns. Please call 16156 for further details.'),)
                ),
                array('group_label_bn' => 'বন্ধু পয়েন্ট', 'group_label_en' => 'Bondhu Point', 'group_name' => 'reference_rejection',
                    'questions' => array(
                        array('question_bn' => 'বন্ধু পয়েন্ট', 'answer_bn' => 'আপনি বিকাশ পেমেন্ট এর মাধ্যমে বন্ধু পয়েন্ট কিনতে পারবেন। বন্ধু পয়েন্ট  কিনতে প্রথমে আপনার বন্ধু অ্যাপ এর মেনু অপশনে যান। মেনুতে পয়েন্ট কিনুন এ প্রবেশ করুন। সেখান থেকে আপনি আপনার বন্ধু পয়েন্ট কিনতে পারবেন বিকাশ পেমেন্টের মাধ্যমে।  অন্যের অনুমতি ব্যাতীত যদি তার বিকাশ একাউন্ট ব্যবহার করে, বন্ধু পয়েন্ট কিনেন তাহলে আপনি নিজে এই প্রতারনার জন্য দায়ী থাকবেন এবং সেবা প্ল্যাটফর্ম  লিমিটেড আপনার বিরুদ্ধে আইনগত ব্যাবস্থা নিতে পারে।  ', 'question_en' => 'Bondhu Point', 'answer_en' => 'You can buy Bondhu Point using your Bkash account. first, go to the side menu of the app, then go to the Buy Point menu, there you can buy the Bondhu Point using bkash. If you use another person\'s bkash account for buying Bondhu Point, then you\'ll be personally liable for this illegal work. Sheba Platform Limited holds the right to take action against you legally.'),)
                ),
                array('group_label_bn' => 'যেকোন ধরণের জিজ্ঞাসা আমি কোথায় করব?', 'group_label_en' => 'General Question', 'group_name' => 'helpline',
                    'questions' => array(
                        array('question_bn' => 'যেকোনো ধরণের জিজ্ঞাসা আমি কোথায় করব?', 'answer_bn' => 'যেকোনো জিজ্ঞাসার জন্য ১৬৫১৬ এ কল করুন।', 'question_en' => 'Whom to contact if I face any issue with this app?', 'answer_en' => 'Call 16516 if you face any difficulties or need any kind of support.'),)),
                array('group_label_bn' => 'টপ-আপ কিভাবে কাজ করে?', 'group_label_en' => 'How TOP-UP works?', 'group_name' => 'TOP-UP', 'questions' => array(
                    array('question_bn' => 'টপ-আপ কিভাবে কাজ করে?', 'answer_bn' => 'বন্ধুতে আপনি যেকোনো মোবাইল অপারেটরে টপ-আপ করার সুযোগ পাচ্ছেন সহজেই। আপনি আপনার বন্ধুর মোবাইলে টপ-আপ করার পাশাপাশি রবি, এয়ারটেল, বাংলালিংকে পাচ্ছেন ২.৫% বোনাস বন্ধু পয়েন্ট এবং গ্রামীণফোনে ১% বোনাস বন্ধু পয়েন্ট।', 'question_en' => 'How TOP-UP works?', 'answer_en' => 'You can do TopUP/mobile recharge using your Bondhu Point, you will get 2.5 % bonus for Robi, Airtel, Banglalink and 1% for Grameenphone in your Bondhu balance for every TopUP. '),),),
                array('group_label_bn' => 'মুভি টিকেট এবং পরিবহন টিকেট কিভাবে কাজ করে?', 'group_label_en' => 'How movie & transport ticket works?', 'group_name' => 'moderator', 'questions' => array(
                    array('question_bn' => 'মুভি টিকেট এবং পরিবহন টিকেট কিভাবে কাজ করে?', 'answer_bn' => ' আপনি আপনার বন্ধু পয়েন্ট ব্যবহার করে আপনার নিজের জন্য অথবা আপনার বন্ধুর জন্য মুভি এবং পরিবহন টিকেট কিনে বন্ধু পয়েন্টে বোনাস পেতে পারেন।', 'question_en' => 'How movie & transport ticket works?', 'answer_en' => 'You can buy movie and transport ticket using your Bondhu Point, as well as you can earn bonus in your Bondhu Point balance.'),

                )));

            return api_response($request, $faqs, 200, ['faqs' => $faqs]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getPartnerPerformanceFaqs(Request $request)
    {
        try {
            $faqs = array(array('question_en' => null, 'question_bn' => 'পারফর্মেন্স বলতে কি বুঝায়?', 'list' => array(array('title_bn' => null, 'answer_bn' => 'আপনি কাস্টমার এর কাছ থেকে যতগুলো অর্ডার গ্রহণ করেছেন তার মধ্যে কতটা সফল ভাবে সম্পন্ন করতে পেরেছেন তাই হচ্ছে পারফর্মেন্স।', 'title_en' => null, 'answer_en' => null, 'asset_type' => 'none', 'emoji' => null, 'range_en' => null, 'range_bn' => null,),)), array('question_en' => null, 'question_bn' => 'কি কি বিষয়ের উপর পারফর্মেন্স নির্ভর করে?', 'list' => array(array('title_bn' => 'সফল ভাবে সম্পন্ন', 'answer_bn' => 'আপনার প্রাপ্ত অর্ডার গুলোর মধ্যে কতগুলো অর্ডার সফলভাবে সম্পন্ন হয়েছে?', 'title_en' => null, 'answer_en' => null, 'asset_type' => 'bullet', 'emoji' => null, 'range_en' => null, 'range_bn' => null,), array('title_bn' => 'কমপ্লেইন ছাড়া সম্পন্ন', 'answer_bn' => 'প্রাপ্ত অর্ডার গুলোর মধ্যে যত গুলো অর্ডার কোন কমপ্লেইন ছাড়া সম্পন্ন হয়েছে।', 'title_en' => null, 'answer_en' => null, 'asset_type' => 'bullet', 'emoji' => null, 'range_en' => null, 'range_bn' => null,), array('title_bn' => 'টাইমলি এক্সেপ্ট', 'answer_bn' => 'প্রাপ্ত অর্ডার গুলোর মধ্যে যতগুলো অর্ডার ২ মিনিটের মধ্যে এক্সেপ্ট করতে পেরেছেন।', 'title_en' => null, 'answer_en' => null, 'asset_type' => 'bullet', 'emoji' => null, 'range_en' => null, 'range_bn' => null,), array('title_bn' => 'সময়মত কাজ শুরু', 'answer_bn' => ' প্রাপ্ত অর্ডার গুলোর মধ্যে যতগুলো অর্ডার শিডিউল অনুজায়ী শুরু করতে পেরেছেন।', 'title_en' => null, 'answer_en' => null, 'asset_type' => 'bullet', 'emoji' => null, 'range_en' => null, 'range_bn' => null,),)), array('question_en' => null, 'question_bn' => 'এই বিষয়গুলো কিভাবে আমাদের উপকার করবে?', 'list' => array(array('title_bn' => null, 'answer_bn' => 'যখনি তুলনামূলক ভাবে আপনার কোন সার্ভিস এর গুণগত মান কমে যাবে তখনি সেই বিষয়গুলো আপনার সামনে দৃশ্যমান হবে। তখন আপনি উল্লিখিত বিষয়গুলো নিয়ে মান উন্নয়নের জন্য কাজ করতে পারবেন।', 'title_en' => null, 'answer_en' => null, 'asset_type' => 'none', 'emoji' => null, 'range_en' => null, 'range_bn' => null,),)), array('question_en' => null, 'question_bn' => 'পারফর্মেন্স কিভাবে পরিমাপ করা হবে?', 'list' => array(array('title_bn' => 'খুব ভালো', 'answer_bn' => 'আপনার সার্ভিস এর গুণগত মান সর্বোচ্চ পর্যায়ে রাখতে সফল হয়েছেন।', 'title_en' => null, 'answer_en' => null, 'asset_type' => 'emoji', 'emoji' => 'very_good', 'range_bn' => '( ৮১% - ১০০% )', 'range_en' => '( 81% - 100% )',), array('title_bn' => 'ভালো', 'answer_bn' => 'আপনার সার্ভিস এর গুণগত মান কাস্টমার এর প্রত্যাশার কাছাকাছি রয়েছে।', 'title_en' => null, 'answer_en' => null, 'asset_type' => 'emoji', 'emoji' => 'good', 'range_bn' => '(৬১% - ৮০%)', 'range_en' => '(61% - 80%)',), array('title_bn' => 'সন্তোষজনক', 'answer_bn' => 'আপনার সার্ভিস এর মান কাস্টমার এর প্রত্যাশার কাছাকাছি নেই।', 'title_en' => null, 'answer_en' => null, 'asset_type' => 'emoji', 'emoji' => 'satisfactory', 'range_bn' => '(৪১% - ৬০%)', 'range_en' => '(41% - 60%)',), array('title_bn' => 'খারাপ', 'answer_bn' => 'আপনার সার্ভিস এর মান খারাপ। মান উন্নয়নের জন্য কাজ করতে হবে।', 'title_en' => null, 'answer_en' => null, 'asset_type' => 'emoji', 'emoji' => 'bad', 'range_bn' => '(২১% - ৪০%)', 'range_en' => '(21% - 40%)',), array('title_bn' => 'খুব খারাপ', 'answer_bn' => 'আপনার সার্ভিস এর মান কাস্টমার কে সার্ভ করার উপযোগী নয়। অনুগ্রহ করে মান উন্নয়নের জন্য কাজ করুন।', 'title_en' => null, 'answer_en' => null, 'asset_type' => 'emoji', 'emoji' => 'very_bad', 'range_bn' => '(০% - ২০%)', 'range_en' => '(0% - 20%)',),)),);
            return api_response($request, $faqs, 200, ['faqs' => $faqs]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getPartnerSmsCampaignFaq($partner, Request $request)
    {
        try {
            $faqs = array(array('question_en' => null, 'question_bn' => 'এসএমএস মার্কেটিং কি?', 'answer_en' => null, 'answer_bn' => 'আপনার ব্যবসার প্রচার-প্রসার করতে এসএমএস মার্কেটিং টুলটি ব্যবহার করুন।'), array('question_en' => null, 'question_bn' => 'কি কি বিষয়ে মার্কেটিং করব?', 'answer_en' => null, 'answer_bn' => 'নতুন অফার, প্রমোশন, ডিসকাউন্ট ইত্যাদি বিষয়ের উপর মার্কেটিং করতে পারেন।'), array('question_en' => null, 'question_bn' => 'এখানে মার্কেটিং কেন করবো?', 'answer_en' => null, 'answer_bn' => 'সহজেই ব্যবসা বার্তা পৌঁছে দিন কাস্টমারের কাছে। আপনার সুবিধা মত সময়ে ও বাজেটে স্বল্পমূল্যে কার্যকরী মার্কেটিং  '), array('question_en' => null, 'question_bn' => 'কাদেরকে এসএমএস পাঠাতে পারব?', 'answer_en' => null, 'answer_bn' => 'আপনার ফোনবুকে থাকা যেকোনো সচল নাম্বারে আপনি এসএমএস পাঠাতে পারবেন। '),);
            return api_response($request, $faqs, 200, ['faqs' => $faqs]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getSubscriptionFaq(Request $request)
    {
        try {
            $faqs = [
                [
                    'question' => 'How Subscription Works',
                    'answer' => null,
                    'list' => ['You can subscribe for a week and month', 'From subscription details you can renew for next billing cycle']
                ],
                [
                    'question' => 'How Payment Works',
                    'answer' => null,
                    'list' => ['Payment must be confirmed before completing subscription', 'You can pay via any payment method available in sheba.xyz']
                ]
            ];
            return api_response($request, $faqs, 200, ['faqs' => $faqs]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function posFaq(Request $request)
    {
        try {
            $faqs = array(array('question_en' => null, 'question_bn' => 'প্রোমো কোড কি?', 'answer_en' => null, 'answer_bn' => 'আপনি আপনার কাস্টমার কে ডিস্কাউন্ট সুবিধা সহ একটি কোড দিতে পারবেন। যেটা ব্যবহার করে কাস্টমার আপনার কাছ থেকে ডিস্কাউন্ট সুবিধা নিতে পারবে।'),
                array('question_en' => null, 'question_bn' => 'প্রোমো কোড কিভাবে তৈরি করবেন?', 'answer_en' => null, 'answer_bn' => 'এসম্যানেজার অ্যাপ এর প্রোমো ও মার্কেটিং সেকশন থেকে প্রোমো কোড এ ট্যাপ করুন। তারপরে আপনার কাস্টমার কে যে সুবিধা দিতে চান সেই অনুযায়ী কনফিগার করে প্রোমো কোড তৈরি করুন।।'),
                array('question_en' => null, 'question_bn' => 'প্রোমো কোড কিভাবে ব্যবহার করবেন?', 'answer_en' => null, 'answer_bn' => 'আপনি সেলস পয়েন্ট থেকে যখন কোন অর্ডার প্লেস করবেন তখন প্রোমো কোড ব্যবহার করার অপশন পাবেন, সেখান থেকে আপনাকে প্রোমো কোড ব্যবহার করতে হবে।'),
                array('question_en' => null, 'question_bn' => 'প্রোমো কোড কাস্টমার এর সাথে কিভাবে শেয়ার করবেন?', 'answer_en' => null, 'answer_bn' => 'প্রোমো কোড তৈরি করার পরে আপনি কাস্টমারকে সরাসরি বলে দিতে পারেন, তাছাড়া আপনি চাইলে এসএমএস অথবা অন্য কোন মাধ্যমে শেয়ার করতে পারেন।'),
                array('question_en' => null, 'question_bn' => ' প্রোমো কোড এর মেয়াদ কত দিন হবে?', 'answer_en' => null, 'answer_bn' => 'আপনি যখন প্রোমো কোড তৈরি করবেন তখন প্রোমো কোড এর মেয়াদ দেয়ার অপশন পাবেন। আপনি যে মেয়াদ সেট করবেন প্রোমো কোড টির মেয়াদ ততদিন থাকবে।'),);
            return api_response($request, $faqs, 200, ['faqs' => $faqs]);

        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);

        }
    }


}
