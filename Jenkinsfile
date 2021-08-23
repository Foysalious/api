pipeline {
    agent any
    stages {
        stage('LAST COMMIT DETAILS') {
            when { branch 'development' }
            steps {
                script {
                    LAST_COMMIT_USER_NAME = sh(script: 'git log -1 --pretty=%an', returnStdout: true).trim()
                    LAST_COMMIT_USER_EMAIL = sh(script: 'git log -1 --pretty=%ae', returnStdout: true).trim()
                    echo "last commit user:${LAST_COMMIT_USER_NAME}."
                    echo "last commit user email:${LAST_COMMIT_USER_EMAIL}."
                }
            }
        }
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
                                execCommand: 'cd /var/www/api && ./bin/test_by_docker.sh',
                                execTimeout: 2100000,
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
                                    remoteDirectory: '/tech_api/public',
                                    remoteDirectorySDF: false,
                                    removePrefix: '',
                                    sourceFiles: '**/api-test-result.xml'
                                ),
                                sshTransfer(
                                    cleanRemote: false,
                                    excludes: '',
                                    execCommand: 'cd /var/www/tech_api && ./bin/test_result_send_to_slack.sh',
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
        stage('DELETE WORKSPACE FILES') {
            steps {
                echo 'Deleting current workspace ...'
                deleteDir() /* clean up our workspace */
            }
        }
        stage('DELETE DOCKER DANGLING IMAGES') {
            when { branch 'master' }
            steps {
                script {
                    sshPublisher(publishers: [
                        sshPublisherDesc(configName: 'api-node-03',
                            transfers: [
                                sshTransfer(
                                    cleanRemote: false,
                                    excludes: '',
                                    execCommand: 'cd /var/www/api && ./bin/remove_dangling_images.sh',
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
    }
}
