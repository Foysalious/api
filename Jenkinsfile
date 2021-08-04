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
        stage('RUN TEST RESULT') {
            when { branch 'development' }
            steps {
                script {
                    sshPublisher(publishers: [
                        sshPublisherDesc(configName: 'development-server',
                            transfers: [sshTransfer(
                                cleanRemote: false,
                                excludes: '',
                                execCommand: 'cd /var/www/api && ./bin/test_by_docker.sh',
                                execTimeout: 3600000,
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
                sshagent(['development-server-ssh']) {
                    sh "scp sheba@103.197.207.30:/var/www/api/results/phpunit/api-test-result.xml ."
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
    }
}
