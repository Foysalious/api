<?php namespace Sheba\Business\CoWorker;

use Sheba\Helpers\ConstGetter;

class Designations
{
    use ConstGetter;

    public static function getDesignations()
    {
        return [
            'Chief Executive Officer (CEO)',
            'Chief Operating Officer (COO)',
            'Chief Financial Officer (CFO)',
            'Chief Marketing Officer (CMO)',
            'Chief Technology Officer (CTO)',
            'Chief Information Officer (CIO)',
            'Chief Legal Officer (CLO)',
            'President',
            'Vice President',
            'Executive Assistant',
            'Managing Director (MD)',
            'Head of Department (HOD)',
            'General Manager',
            'Assistant Manager',
            'Sr. Manager',
            'Manager',
            'Product Manager',
            'Sr. Product Manager',
            'Jr. Product Manager',
            'Project Manager',
            'Sr. Project Manager',
            'Jr. Project Manager',
            'Brand Manager',
            'Regional Manager',
            'Business Analyst',
            'Data analyst',
            'Analyst',
            'Marketing Specialist',
            'Accountant',
            'Sales Representative',
            'Key Account Manager',
            'Customer Service Representative',
            'Sr. Executive',
            'Executive',
            'Jr. Executive',
            'Intern',
            'Architect',
            'Consultant',
            'Designer',
            'Programmer',
            'Technician',
            'Engineer',
            'Software Engineer',
            'Sr. Software Engineer',
            'Jr. Software Engineer',
            'UI/UX Designer',
            'Sr. UI/UX Designer',
            'Jr. UI/UX Designer',
            'UX designer',
            'Developer',
            'App Developer',
            'IT Support Manager',
            'IT Support Specialist',
            'Security Specialist',
            'Software Quality Assurance (SQA)',
            'Sr. SQA',
            'Jr. SQA',
            'Web Developer',
            'Sr. Web Developer',
            'Jr. Web Developer'
        ];
    }
}