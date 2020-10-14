pipeline {
    agent any
    environment {
        CONTAINER_NAME = "${sh(script: './bin/parse_env.sh | echo $CONTAINER_NAME', returnStdout: true)}"
    }
    stages {
        stage('Make ENV File') {
            steps {
                withCredentials([
                    string(credentialsId: 'VAULT_ROLE_ID', variable: 'VAULT_ROLE_ID'),
                    string(credentialsId: 'VAULT_SECRET_ID', variable: 'VAULT_SECRET_ID')
                ]) {
                    sh './bin/make_env.sh'
                }
            }
        }
        stage('Pull In Development') {
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
                                    remoteDirectory: 'sockets',
                                    remoteDirectorySDF: false,
                                    removePrefix: '',
                                    sourceFiles: '**/*.env'
                                ),
                                sshTransfer(
                                    cleanRemote: false,
                                    excludes: '',
                                    execCommand: 'cd /var/www/sockets && mv development.env .env && sudo ./bin/deploy.sh development',
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
        stage('Build For Production') {
            when { branch 'master' }
            steps {
                 sh './bin/build.sh'
            }
        }
        stage('Deploy To Production') {
            when { branch 'master' }
            steps {
                script {
                    sshPublisher(publishers: [
                        sshPublisherDesc(configName: 'production-server',
                            transfers: [
                                sshTransfer(
                                    cleanRemote: false,
                                    excludes: '',
                                    execCommand: 'cd /var/www/sockets && sudo ./bin/deploy.sh master',
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
        stage('Clean Up Build') {
            when { branch 'master' }
            steps {
                sh './bin/remove_build.sh'
            }
        }
        stage('Delete Workspace Files') {
            steps {
                echo 'Deleting current workspace ...'
                deleteDir() /* clean up our workspace */
            }
        }
    }
}
