const join_commands = arr => (arr || []).join(' && ');

const isWin = process.platform === "win32";

let pre_commit = [
    //"echo $USER",
    //"exit 1"
];

let commitlint_script = "node_modules/@sheba/commitlint/index.js";
let commit_msg_file = isWin ? "%HUSKY_GIT_PARAMS%" : "$HUSKY_GIT_PARAMS";
module.exports = {
    'hooks': {
        'pre-commit': join_commands(pre_commit),
        'commit-msg': "node " + commitlint_script + " -E " + commit_msg_file,
    }
};