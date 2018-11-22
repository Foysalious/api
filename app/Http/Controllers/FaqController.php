<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function getAffiliateFaqs(Request $request)
    {
        try {
            $faqs = array(
                array(
                    'group_label_bn' => 'সেবা বন্ধু',
                    'group_label_en' => 'Sheba Bondhu',
                    'group_name' => 'sheba_bondhu',
                    'questions' => array(
                        array(
                            'question_bn' => 'সেবা বন্ধু কী?',
                            'answer_bn' => 'সেবা বন্ধু হচ্ছে এমন একটি অ্যাপ যার মাধ্যমে আপনি আপনার বন্ধুকে হেল্প করার পাশাপাশি সহজে টাকা আয় করতে পারবেন। \nসেবা বন্ধুতে আপনি সার্ভিস, আপনার ব্যবসায়ী বন্ধুকে রেফার অথবা টপ আপ এর মাধ্যমে টাকা আয় করতে পারবেন।',
                            'question_en' => 'What is SHEBA Bondhu APP?',
                            'answer_en'=> 'Sheba Bondhu is an APP which enable you to earn money by helping your friend. In Sheba Bondhu APP you can earn money by referring Service, Businessman Friend or top-up to your friend.'
                        ),
                        array(
                            'question_bn' => 'আমার একাউন্ট ভেরিফাইড হয়নি কেন?',
                            'answer_bn' => 'আপনার একাউন্টটি ভেরিফাইড করতে আপনাকে অবশই আপনার বিকাশ নাম্বারটি প্রদান করতে হবে। তার পরও যদি ভেরিফাইড না হয় তবে ১৬৫১৬ এ যোগাযোগ করুন।',
                            'question_en' => 'Why my account is not verified yet?',
                            'answer_en'=> 'You need to input your BKash Number to verify your account. If it not verified after that then you need to 1516 for help.'
                        ),
                    )
                ),
                array(
                    'group_label_bn' => 'সার্ভিস রেফার কিভাবে কাজ করে?',
                    'group_label_en' => 'How does Service Refer work',
                    'group_name' => 'service_refer',
                    'questions' => array(
                        array(
                            'question_bn' => 'সার্ভিস রেফার কিভাবে কাজ করে?',
                            'answer_bn' => 'আপনার বন্ধুর যদি কোন সার্ভিস প্রয়োজন হয় আপনি তার জন্য সেবাতে বন্ধু অ্যাপ এর মাধ্যমে রেফের করতে পারবেন। আপনার রেফারকৃত বন্ধুকে সেবা হেল্প ডেস্ক হতে দ্রুত কল করা হবে এবং তার সার্ভিসটি প্রদান এর জন্য সেবাতে একটি অর্ডার প্লেস করা হবে। একজন সেবা রেজিস্টার্ড সার্ভিস প্রোভাইডার আপনার বন্ধুর নিকট পৌঁছে যাবে।',
                            'question_en' => 'How does service referral work?  ',
                            'answer_en'=> 'You can refer service for your friend in sheba by this APP. Within a short time, your referred friend will receive a call from Sheba Help Desk and an order will placed. Sheba registered service provider will go to your friend to give his service. '
                        ),
                        array(
                            'question_bn' => 'সার্ভিস রেফার এর মাধ্যমে কিভাবে টাকা আয় করবেন?',
                            'answer_bn' => 'বন্ধু অ্যাপ এর মাধ্যমে সেবা তে আপনি আপনার পরিচিত জনের জন্য সার্ভিস রেফার করতে পারবেন। আগ্রহী গ্রাহকের নাম, ফোন নাম্বার ও কোন সার্ভিসটি লাগবে এই তথ্যগুলো দিয়ে রেফার করুন। প্রতিটি রেফার সফল ভাবে সম্পন্ন হতেই আপনার ওয়ালেট এ ১০ টাকা থেকে ১০০০ টাকা পর্যন্ত পাবেন।',
                            'question_en' => 'How to earn money by referring service for your friend? ',
                            'answer_en' => 'You can refer a service to your friend via Sheba Bondhu APP. By giving information about Customer Name, Phone and Needed Service name you can refer your friend. On every successful refer you will earn 5% of order amount as bonus in your Bondhu moneybag'
                        ),
                    )
                ),
                array(
                    'group_label_bn' => 'কিভাবে সার্ভিস প্রোভাইডার রেফার কাজ করে?',
                    'group_label_en' => 'How Service provider referral work?',
                    'group_name' => 'service_provider',
                    'questions' => array(
                        array(
                            'question_bn' => 'কিভাবে সার্ভিস প্রোভাইডার রেফার কাজ করে?',
                            'answer_bn' => 'আপনি কোন ব্যাবসায়ী বন্ধুকে রেফার করলে সেই রেফার ক্রিত নম্বার এ একটি মাসেজ এর মাধ্যমে সেবা মানেজার অ্যাপ এর লিঙ্ক পাঠানো হবে। আপনার বন্ধুকে সেই অ্যাপটি ডাউনলোড করে অ্যাপটি ইন্সটল করতে হবে। ডাউনলোড সম্পন্ন হলে অ্যাপ এর মাধ্যমে সার্ভিস প্রোভাইডার হিসাবে রেজিস্টার করতে হবে। সকল আবশ্যক তথ্য প্রদানের পর আপনার রেফের ক্রিত বন্ধুটি Ready to Verified হবে। সে সেবা দ্বারা ভেরিফাইড হওয়ার পর আপনি প্রথম কিস্তি বোনাস পাবেন। আপনার বন্ধুটি প্রথম দুটি কাজ সফল ভাবে সম্পন করলে আপনি বাকি বোনাস পায়ে যাবেন।',
                            'question_en' => 'How does service referral work?  ',
                            'answer_en'=> 'When you refer your business man friend, it will develop and send a massage to your friend phone with a link of Sheba Manager APP. Manger App need to download and install to his phone. He needs to get registered by giving all the valid necessary information. Sheba team will verify the service provider and he will able to serve. After completing verification, you will get first step of bonus. After completing two successful job Bondhu will get the bonus.'
                        ),
                        array(
                            'question_bn' => 'কিভাবে আপনার ব্যাবসায়ী বন্ধুকে রেফার করে টাকা আয় করবেন?',
                            'answer_bn' => 'আপনি আপনার বন্ধু অ্যাপ এর মাধ্যমে আপনার ব্যাবসায়ী বন্ধুকে রেফার করতে পারবেন। আপনার বন্ধুর নাম, ফোন নং এবং সার্ভিস নাম প্রদান করে আপনি তাকে সেবাতে রেফার করতে পারবেন। আর প্রতিটি সফল রেফের এ আপনি পাবেন ১৪০ টাকা বোনাস। এই বোনাস আপনি পাবেন দুটি কিস্তিতে- \n
- রেজিস্টারড বন্ধুটি যখন সেবা দ্বারা ভেরিফাইড হবে তখন বোনাস পাবেন ৪২টাকা। \n
- দুটি সফল সার্ভিস প্রদান করলে বন্ধু পাবে বাকি ৯৮ টাকা।',
                            'question_en' => 'How to earn money by referring service for your friend? ',
                            'answer_en' => 'You can refer a service to your friend via Sheba Bondhu APP. By giving information about Customer Name, Phone and Needed Service name you can refer your friend. On every successful refer you will earn 5% of order amount as bonus in your Bondhu moneybag'
                        ),
                    )
                ),
                array(
                    'group_label_bn' => 'মানিব্যাগ কি ?',
                    'group_label_en' => 'What is a Money bag?',
                    'group_name' => 'money_bag',
                    'questions' => array(
                        array(
                            'question_bn' => 'কিভাবে সার্ভিস প্রোভাইডার রেফার কাজ করে?',
                            'answer_bn' => 'মানিব্যাগ হচ্ছে আপনার ভার্চুয়াল ওয়ালেট। মানিব্যাগে যেই টাকার পরিমান দেখাবে সেটি হচ্ছে আপনার আয় করা টাকার পরিমান।',
                            'question_en' => 'What is a Money bag?  ',
                            'answer_en'=> 'When you refer your business man friend, it will develop and send a massage to your friend phone with a link of Sheba Manager APP. Manger App need to download and install to his phone. He needs to get registered by giving all the valid necessary information. Sheba team will verify the service provider and he will able to serve. After completing verification, you will get first step of bonus. After completing two successful job Bondhu will get the bonus.'
                        ),
                        array(
                            'question_bn' => 'মানিব্যাগ হতে কিভাবে নগদ টাকা উত্তোলন করব?',
                            'answer_bn' => 'এই অ্যাপ থেকে আয়কৃত টাকা প্রতি সপ্তাহে একবার আপনার দেয়া বিকাশ অ্যাকাউন্ট-এ ট্রান্সফার হয়ে যাবে। নিকটস্থ বিকাশ পয়েন্ট থেকে বিকাশ অ্যাকাউন্টের টাকা নগদ উত্তোলন করতে পারবেন। তবে টাকা উত্তোলন করতে হলে আপনার মানিব্যাগে সর্বনিম্ন ১০০ টাকা থাকতে হবে।।',
                            'question_en' => 'How can I withdraw from my Money bag? ',
                            'answer_en' => 'You will get the payment in your verified bKash no. that you provided in this app. You can cash out the amount from the nearest bKash point.'
                        ),
                    )
                ),
                array(
                    'group_label_bn' => 'একাউন্ট স্থগিত হবার কারণ কী?',
                    'group_label_en' => 'THE REASON FOR SUSPENSION OF ACCOUNTS',
                    'group_name' => 'account_suspension',
                    'questions' => array(
                        array(
                            'question_bn' => 'আমার একাউন্ট স্থগিত হবার কারন কি কি?',
                            'answer_bn' => 'সার্ভিস রেফার এর ক্ষেত্রে আপনি যদি ১০ বার ভুল অথবা মিথ্যা রেফার করেন তবে আপনার একাউন্টটি স্থগিত করা হবে। আপনি দিনে ২০টির বেশি সার্ভিস রেফার করতে পারবেন না।',
                            'question_en' => 'What are the reasons behind my suspension ?  ',
                            'answer_en'=> 'Your account will be suspended by giving 10 fake or invalid reference. Your referral limit is 20 service(s) per day.'
                        ),
                        array(
                            'question_bn' => 'আমার একাউন্টটি কতদিন স্থগিত থাকবে?',
                            'answer_bn' => 'আপনার একাউন্টটি সর্বোচ্চ ৩ দিন স্থগিত থাকবে।',
                            'question_en' => 'How long my account will be suspended ? ',
                            'answer_en' => 'Suspension will go away after 3 days automatically.'
                        ),
                    )
                ),
                array(
                    'group_label_bn' => 'রেফারেন্স রিজেকশন',
                    'group_label_en' => 'REFERENCE REJECTION',
                    'group_name' => 'reference_rejection',
                    'questions' => array(
                        array(
                            'question_bn' => 'আমার রেফারেল রিজেকশন হলে কি করবো?',
                            'answer_bn' => 'যেকোন হেল্পের জন্য  আমাদের গ্রাহক সেবা নম্বর ১৬৫১৬ এ কল করলে সমাধান পেয়ে যাবেন।',
                            'question_en' => 'What to do if I get rejected ?  ',
                            'answer_en'=> 'Contact with our call center(16516) for reactivation of your account.'
                        ),
                    )
                ),
                array(
                    'group_label_bn' => 'এজেন্ট ও এম্বাসেডর',
                    'group_label_en' => 'AGENT &amp; AMBASSADOR',
                    'group_name' => 'agent_ambassador',
                    'questions' => array(
                        array(
                            'question_bn' => 'এজেন্ট কি?',
                            'answer_bn' => 'যেবাতে রেজিস্টার্ড সকল বন্ধুকে সেবা বন্ধু এজেন্ট বলা হয়। সেবা বন্ধু এজেন্টগণ নিজে স্বাধীনভাবে বা একজন এম্বাসেডর এর সাথে থেকে রেফার করতে পারবেন।',
                            'question_en' => 'What is Agent?  ',
                            'answer_en'=> 'All agents registered in Sheba Bondhu app will count as an agent. Sheba Bondhu Agent can able to operate independently as an agent, or they can operate under an ambassador.'
                        ),
                        array(
                            'question_bn' => 'এম্বাসেডর কি?',
                            'answer_bn' => 'সেবা বন্ধু অ্যাপ এ এম্বাসেডর হচ্ছে এজেন্ট নিয়োগকারি। একজন এম্বাসেডর একজন এজেন্ট ও। তবে এম্বাসেডর তার অধীনে এজেন্ট নিয়োগ করতে পারেন। এজেন্ট তার সেটিংস্‌ এর মাই অ্যাকাউন্ট অপশন থেকে যে কোন আম্বাসেডর এর কোড প্রবেশ এর মাধ্যমে তার এজেন্ট হতে পারবেন। এজেন্টদের প্রতিটি সফল সার্ভিস রেফারে মুনাফার ২০%  বোনাস এবং সার্ভিস প্রোভাইডার রেফার এ ৬০ টাকা বোনাস পাবেন।',
                            'question_en' => 'What is Ambassador?  ',
                            'answer_en'=> 'In Sheba, Bondhu app ambassador is agent recruiter. On the other hand, he is also an agent. An agent can add his ambassador from my accounts option. From every successful service refer ambassador will receive 20% of profit as bonus and every successful service provider refer he will get 60tk as a bonus.'
                        ),
                    )
                ),
                array(
                    'group_label_bn' => 'হেল্পলাইন',
                    'group_label_en' => 'HELPLINE',
                    'group_name' => 'helpline',
                    'questions' => array(
                        array(
                            'question_bn' => 'যেকোনো ধরণের জিজ্ঞাসা আমি কোথায় করব?',
                            'answer_bn' => 'যেকোনো জিজ্ঞাসার জন্য ১৬৫১৬ এ কল করুন।',
                            'question_en' => 'Whom to contact if I face any issue with this app?',
                            'answer_en'=> 'Call 16516 if you face any difficulties or need any kind of support.'
                        ),
                    )
                ),
                array(
                    'group_label_bn' => 'প্রোমোকোড',
                    'group_label_en' => 'PROMO CODE',
                    'group_name' => 'promo_code',
                    'questions' => array(
                        array(
                            'question_bn' => 'আমার কোডটি কিভাবে কাজ করবে?',
                            'answer_bn' => 'এজেন্ট সংগ্রহ- এজেন্ট সংগ্রহ করার জন্য আপনার কোডটি তাদের সাথে শেয়ার করুন। আপনার এজেন্ট বন্ধু অ্যাপ দ্বারা রেফার করলে, প্রত্যেকটি সফল রেফার এর জন্য আপনি সেবার সার্ভিস চার্জ থেকে ২০% (১০ টাকা থেকে সর্বোচ্চ ২০০ টাকা) পাবেন। আপনি যতবেশি আপনার এজেন্ট সংগ্রহ করবেন, আপনার লাভবান হবার সম্ভাবনা তত বেড়ে যাবে। বেশি বেশি এজেন্ট সংগ্রহ করুন, বেশি বেশি উপার্জন করুন। ',
                            'question_en' => 'How my code will work?',
                            'answer_en'=> 'Connect Agents- Share your code with your friends and connect them with your account.When your agents start referring to Sheba through Bondhu app, at each successful reference you will get 20% of benefits from Sheba service charge (at least 10 BDT up-to 200 BDT). The more you connect your friends, the more you chance will be increased to be benefited. Start connecting more agents and earn more by your agents.'
                        ),
                        array(
                            'question_bn' => 'প্রোমোকোড ব্রডকাস্টিং',
                            'answer_bn' => 'প্রোমোকোড ব্রডকাস্টিং- আপনি আপনার প্রোমোকোডটি নতুন কাস্টমারদের বিতরণ করতে পারবেন। নতুন কাস্টমার, তার কাস্টমার অ্যাপদ্বারা অর্ডার প্লেস করার সময় আপনার প্রদত্ত কোডটি প্রোমোকোড হিসেবে ব্যাবহার করে সর্বোচ্চ ১০০ টাকা পর্যন্ত ডিস্কাউন্ট পাবে। এক্ষেত্রে প্রত্যেকটি সফল অর্ডার এর জন্য আপনি সেবার সার্ভিস চার্জ থেকে ৫০% (২০ টাকা থেকে সর্বোচ্চ ২০০ টাকা) পাবেন।',
                            'question_en' => 'Promo code broadcasting',
                            'answer_en'=> 'Promo code broadcasting- You can also distribute your promo code to the new users who are willing to place order via customer app. If new users use your ambassador code as a promo code while placing order. Customer will get maximum 100 BDT discount for his order. For the  each successful order you will get 50% of benefits from Sheba service charge (at least 20 BDT up-to 200 BDT) from each orders by new users.'
                        ),
                    )
                ),

            );
            return api_response($request, $faqs, 200, ['faqs' => $faqs]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}
