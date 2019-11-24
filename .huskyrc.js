// const join_commands = arr => (arr || []).join(' && ');
//
// const isWin = process.platform === "win32";
//
// let commitlint_script = "node_modules/@sheba/commitlint/index.js";
// let commit_msg_file = isWin ? "%HUSKY_GIT_PARAMS%" : "$HUSKY_GIT_PARAMS";
// module.exports = {
//     'hooks': {
//         'commit-msg': "node " + commitlint_script + " -E " + commit_msg_file,
//     }
// };