// Utility method.
def sendMessage = { color, specificMessage ->
    // Print a message to the console and to Slack.
    header = "Job <${env.JOB_URL}|${env.BRANCH_NAME}> <${env.JOB_DISPLAY_URL}|(Blue)>"
    header += " build <${env.BUILD_URL}|${env.BUILD_DISPLAY_NAME}> <${env.RUN_DISPLAY_URL}|(Blue)>:"
    message = "${header}\n${specificMessage}"
    if (lastCommit.equals(ancestorCommit)) {
        // Get last commit if we do not have a distinct ancestor.
        commitHashes = [sh(script: "git log -1 --pretty=%H", returnStdout: true).trim()]
    } else {
        // Get max 5 commits since ancestor.
        commitHashes = sh(script: "git rev-list -5 ${ancestorCommit}..", returnStdout: true).trim().tokenize('\n')
    }
    for (commit in commitHashes) {
        author = sh(script: "git log -1 --pretty=%an ${commit}", returnStdout: true).trim()
        commitMsg = sh(script: "git log -1 --pretty=%B ${commit}", returnStdout: true).trim()
        message += " Commit by <@${author}> (${author}): ``` ${commitMsg} ``` "
    }
    echo "Message ${message}"

    /* (optional snippet)
    // Send a Slack message. (Note that you need to configure a Slack access token in the Jenkins system settings).
    slackSend channel: 'yourchannelid', teamDomain: 'yourdomain', color: color, message: message, failOnError: true
    */
}

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
