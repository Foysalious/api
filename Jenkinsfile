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
        stage('Check') {
            steps {
                script {
                    // Determine ancestor commit. This is used for two things:
                    // 1) Determine a list of commits for a build result message.
                    // 2) Check if we committed an empty branch, so we can skip the build.
                    //
                    // Note, epic is added for demonstrative purposes: epic branches are temporary develop branches,
                    // this can be any prefix you use for branches that can spawn feature branches.
                    parentBranches = '$(git branch -a --list origin/master origin/develop origin/epic/*)'
                    ancestorCommit = sh(
                            script: "git merge-base HEAD ${parentBranches}",
                            returnStdout: true).trim()
                    lastCommit = sh(script: 'git log -1 --pretty=%H', returnStdout: true).trim()
                    echo "==> Common ancestor is ${ancestorCommit}, last commit is ${lastCommit}."

                    // Check if the branch is empty, or in other words, has no new commits.
                    // If so, fail the build to skip it.
                    if (lastCommit.equals(ancestorCommit)) {
                        // Only skip the build when we are not an ancestor branch,
                        // because we always want to run those.
                        if (!(env.BRANCH_NAME.split('/')[0] in ['master', 'develop', 'epic'])) {
                            env.SKIP_BUILD = 'yes'
                            error('Skipping build, branch contains no new commits.')
                        }
                    }
                }
            }
        }
    }
}
