pipeline {
    agent any

    stages {
        stage('MAKE ENV FILE') {
            steps {
                withCredentials([
                    string(credentialsId: 'VAULT_ROLE_ID', variable: 'VAULT_ROLE_ID'),
                    string(credentialsId: 'VAULT_SECRET_ID', variable: 'VAULT_SECRET_ID')
                ]) {
                    sh './bin/make_env.sh'
                    sh './bin/parse_env.sh'
                }
            }
        }
        stage('PULL IN DEVELOPMENT') {
            when { branch 'development' }
            steps {
                script {
                    sshPublisher(publishers: [
                        sshPublisherDesc(configName: 'development-server',
                            transfers: [
                                sshTransfer(
                                    cleanRemote: false,
                                    excludes: '',
                                    execCommand: '',
                                    execTimeout: 120000,
                                    flatten: false,
                                    makeEmptyDirs: false,
                                    noDefaultExcludes: false,
                                    patternSeparator: '[, ]+',
                                    remoteDirectory: 'api',
                                    remoteDirectorySDF: false,
                                    removePrefix: '',
                                    sourceFiles: '**/*.env'
                                ),
                                sshTransfer(
                                    cleanRemote: false,
                                    excludes: '',
                                    execCommand: 'cd /var/www/api && mv development.env .env && ./bin/deploy.sh development',
                                    execTimeout: 120000,
                                    flatten: false,
                                    makeEmptyDirs: false,
                                    noDefaultExcludes: false,
                                    patternSeparator: '[, ]+',
                                    remoteDirectory: '',
                                    remoteDirectorySDF: false,
                                    removePrefix: '',
                                    sourceFiles: ''
                                )
                            ],
                            usePromotionTimestamp: false,
                            useWorkspaceInPromotion: false,
                            verbose: false
                        )]
                    )
                }
            }
        }
        stage('BUILD FOR PRODUCTION - FOR DOCKER') {
            when { branch 'master' }
            steps {
                 sh './bin/copy_needed_auth.sh'
                 sh './bin/build.sh'
            }
        }
        stage('DEPLOY TO PRODUCTION - FOR API NODE 04') {
            when { branch 'master' }
            steps {
                script {
                    sshPublisher(publishers: [
                        sshPublisherDesc(configName: 'api-node-04',
                            transfers: [
                                sshTransfer(
                                    cleanRemote: false,
                                    excludes: '',
                                    execCommand: 'cd /var/www/api && sudo ./deploy.sh',
                                    execTimeout: 300000,
                                    flatten: false,
                                    makeEmptyDirs: false,
                                    noDefaultExcludes: false,
                                    patternSeparator: '[, ]+',
                                    remoteDirectory: '',
                                    remoteDirectorySDF: false,
                                    removePrefix: '',
                                    sourceFiles: ''
                                )
                            ],
                            usePromotionTimestamp: false,
                            useWorkspaceInPromotion: false,
                            verbose: true
                        )]
                    )
                }
            }
        }
        stage('DEPLOY TO PRODUCTION - FOR API NODE 01') {
            when { branch 'master' }
            steps {
                script {
                    sshPublisher(publishers: [
                        sshPublisherDesc(configName: 'api-node-01',
                            transfers: [
                                sshTransfer(
                                    cleanRemote: false,
                                    excludes: '',
                                    execCommand: 'cd /var/www/api && sudo ./deploy.sh',
                                    execTimeout: 300000,
                                    flatten: false,
                                    makeEmptyDirs: false,
                                    noDefaultExcludes: false,
                                    patternSeparator: '[, ]+',
                                    remoteDirectory: '',
                                    remoteDirectorySDF: false,
                                    removePrefix: '',
                                    sourceFiles: ''
                                )
                            ],
                            usePromotionTimestamp: false,
                            useWorkspaceInPromotion: false,
                            verbose: true
                        )]
                    )
                }
            }
        }
        stage('DEPLOY TO PRODUCTION - FOR API NODE 02') {
            when { branch 'master' }
            steps {
                script {
                    sshPublisher(publishers: [
                        sshPublisherDesc(configName: 'api-node-02',
                            transfers: [
                                sshTransfer(
                                    cleanRemote: false,
                                    excludes: '',
                                    execCommand: 'cd /var/www/api && sudo ./deploy.sh',
                                    execTimeout: 300000,
                                    flatten: false,
                                    makeEmptyDirs: false,
                                    noDefaultExcludes: false,
                                    patternSeparator: '[, ]+',
                                    remoteDirectory: '',
                                    remoteDirectorySDF: false,
                                    removePrefix: '',
                                    sourceFiles: ''
                                )
                            ],
                            usePromotionTimestamp: false,
                            useWorkspaceInPromotion: false,
                            verbose: true
                        )]
                    )
                }
            }
        }
        stage('DEPLOY TO PRODUCTION - FOR PRODUCTION SERVER') {
            when { branch 'master' }
            steps {
                script {
                    sshPublisher(publishers: [
                        sshPublisherDesc(configName: 'production-server',
                            transfers: [
                                sshTransfer(
                                    cleanRemote: false,
                                    excludes: '',
                                    execCommand: 'cd /var/www/api && sudo ./deploy.sh',
                                    execTimeout: 300000,
                                    flatten: false,
                                    makeEmptyDirs: false,
                                    noDefaultExcludes: false,
                                    patternSeparator: '[, ]+',
                                    remoteDirectory: '',
                                    remoteDirectorySDF: false,
                                    removePrefix: '',
                                    sourceFiles: ''
                                )
                            ],
                            usePromotionTimestamp: false,
                            useWorkspaceInPromotion: false,
                            verbose: true
                        )]
                    )
                }
            }
        }
        stage('DEPLOY TO PRODUCTION - FOR API NODE 03') {
            when { branch 'master' }
            steps {
                script {
                    sshPublisher(publishers: [
                        sshPublisherDesc(configName: 'api-node-03',
                            transfers: [
                                sshTransfer(
                                    cleanRemote: false,
                                    excludes: '',
                                    execCommand: 'cd /var/www/api && ./bin/deploy.sh master',
                                    execTimeout: 300000,
                                    flatten: false,
                                    makeEmptyDirs: false,
                                    noDefaultExcludes: false,
                                    patternSeparator: '[, ]+',
                                    remoteDirectory: '',
                                    remoteDirectorySDF: false,
                                    removePrefix: '',
                                    sourceFiles: ''
                                )
                            ],
                            usePromotionTimestamp: false,
                            useWorkspaceInPromotion: false,
                            verbose: false
                        )]
                    )
                }
            }
        }
        stage('DEPLOY TO RELEASE') {
            when { branch 'release' }
            steps {
                script {
                    sshPublisher(publishers: [
                        sshPublisherDesc(configName: 'stage-server',
                            transfers: [
                                sshTransfer(
                                    cleanRemote: false,
                                    excludes: '',
                                    execCommand: 'cd /var/www/api && ./bin/deploy.sh release',
                                    execTimeout: 600000,
                                    flatten: false,
                                    makeEmptyDirs: false,
                                    noDefaultExcludes: false,
                                    patternSeparator: '[, ]+',
                                    remoteDirectory: '',
                                    remoteDirectorySDF: false,
                                    removePrefix: '',
                                    sourceFiles: ''
                                )
                            ],
                            usePromotionTimestamp: false,
                            useWorkspaceInPromotion: false,
                            verbose: true
                        )]
                    )
                }
            }
        }
        stage('CLEAN UP BUILD') {
            when { branch 'master' }
            steps {
                sh './bin/remove_build.sh'
            }
        }
        stage('RUN TEST RESULT') {
            when { branch 'development' }
            steps {
                script {
                    sshPublisher(publishers: [
                        sshPublisherDesc(configName: 'testing-server',
                            transfers: [sshTransfer(
                                cleanRemote: false,
                                excludes: '',
                                execCommand: 'cd /var/www/api && ./bin/deploy.sh development && ./bin/generate_author_for_test.sh && ./bin/test_by_docker_on_parallel_mode.sh',
                                execTimeout: 21000000,
                                flatten: false,
                                makeEmptyDirs: false,
                                noDefaultExcludes: false,
                                patternSeparator: '[, ]+',
                                remoteDirectory: '',
                                remoteDirectorySDF: false,
                                removePrefix: '',
                                sourceFiles: ''
                            )],
                            usePromotionTimestamp: false,
                            useWorkspaceInPromotion: false,
                            verbose: true
                        )]
                    )
                }
            }
        }
        stage('TEST RESULT TO DEPLOYMENT SERVER') {
            when { branch 'development' }
            steps {
                sshagent(['testing-server-ssh']) {
                    sh "scp -P 2222 testing@103.197.207.58:/var/www/api/results/phpunit/api-test-result.xml ."
                    sh "scp -P 2222 testing@103.197.207.58:/var/www/api/results/test_case_author.json ."
                }
            }
        }
        stage('SEND TEST RESULT TO TECH-ALERTS') {
            when { branch 'development' }
            steps {
                script {
                    sshPublisher(publishers: [
                        sshPublisherDesc(configName: 'production-server-on-premises',
                            transfers: [
                                sshTransfer(
                                    cleanRemote: false,
                                    excludes: '',
                                    execCommand: '',
                                    execTimeout: 120000,
                                    flatten: false,
                                    makeEmptyDirs: false,
                                    noDefaultExcludes: false,
                                    patternSeparator: '[, ]+',
                                    remoteDirectory: '/tech_api/public/test_results/api',
                                    remoteDirectorySDF: false,
                                    removePrefix: '',
                                    sourceFiles: '**/test_case_author.json'
                                ),
                                sshTransfer(
                                    cleanRemote: false,
                                    excludes: '',
                                    execCommand: '',
                                    execTimeout: 120000,
                                    flatten: false,
                                    makeEmptyDirs: false,
                                    noDefaultExcludes: false,
                                    patternSeparator: '[, ]+',
                                    remoteDirectory: '/tech_api/public/test_results/api/migrations',
                                    remoteDirectorySDF: false,
                                    removePrefix: '',
                                    sourceFiles: '**/api-test-result.xml'
                                ),
                                sshTransfer(
                                    cleanRemote: false,
                                    excludes: '',
                                    execCommand: 'cd /var/www/tech_api && ./bin/rename_test_result_file.sh api && ./bin/test_migration_run.sh api',
                                    execTimeout: 3600000,
                                    flatten: false,
                                    makeEmptyDirs: false,
                                    noDefaultExcludes: false,
                                    patternSeparator: '[, ]+',
                                    remoteDirectory: '',
                                    remoteDirectorySDF: false,
                                    removePrefix: '',
                                    sourceFiles: ''
                                )
                            ],
                            usePromotionTimestamp: false,
                            useWorkspaceInPromotion: false,
                            verbose: true
                        )]
                    )
                }
            }
        }
    }
}
