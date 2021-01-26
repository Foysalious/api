pipeline {
    agent any
    stages {
        stage('LAST COMMIT DETAILS') {
            when { branch 'development' }
            steps {
                lastCommitUserName  = sh(script: 'git log -1 --pretty=%an', returnStdout: true).trim()
                lastCommitUserEmail = sh(script: 'git log -1 --pretty=%ae', returnStdout: true).trim()
                echo "last commit user:${lastCommitUserName}."
                echo "last commit user email:${lastCommitUserEmail}."
            }
        }
        stage('RUN TEST RESULT') {
            when { branch 'development' }
            steps {
                script {
                    sshPublisher(publishers: [
                        sshPublisherDesc(configName: 'development-server',
                            transfers: [
                                sshTransfer(
                                    cleanRemote: false,
                                    excludes: '',
                                    execCommand: 'cd /var/www/api && ./bin/test_by_docker.sh SingleTopUpTest',
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
        stage('TEST RESULT TO DEPLOYMENT SERVER') {
            when { branch 'development' }
            steps {
                sshagent(['development-server-ssh']) {
                    sh "scp sheba@103.197.207.30:/var/www/api/results/phpunit/api-test-result.xml /var/lib/jenkins/sheba/test-results/api"
                }
            }
        }
        stage('SEND TEST RESULT TO TECH-ALERTS') {
            when { branch 'development' }
            steps {
                script {
                    sshPublisher(publishers: [
                        sshPublisherDesc(configName: 'stage-server',
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
                                    remoteDirectory: '/var/www/tech_alerts/public',
                                    remoteDirectorySDF: false,
                                    removePrefix: '',
                                    sourceFiles: '/var/lib/jenkins/sheba/test-results/api/api-test-result.xml'
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
    }
}
