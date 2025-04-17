const fs = require('fs');
const axios = require('axios');

if (fs.existsSync("projects")) fs.rmSync("projects", { recursive: true });
fs.mkdirSync("projects");

(async () => {
    console.log("Loading access token...");
    const token = fs.readFileSync("token.txt").toString().trim();

    console.log("Fetching projects...");
    const projects = (await axios.get("https://ci.minteck.org/app/rest/projects", { headers: {"Authorization": "Bearer " + token} })).data.project;
    fs.writeFileSync("projects.json", JSON.stringify(projects, null, 4));

    for (const prj of projects) {
        let prjData = {
            id: prj.id,
            name: (prj.parentProjectId !== "_Root" ? prj.parentProjectId + " " : "") + prj.name,
            description: prj.description ?? null,
            web: prj.webUrl,
            channels: []
        };

        if (prj.id === "_Root") continue;
        console.log("Fetching: " + prj.id + ", deploy channels");

        const channels = (await axios.get("https://ci.minteck.org/app/rest/projects/id:" + prj.id, { headers: {"Authorization": "Bearer " + token} })).data.buildTypes.buildType;

        for (const channel of channels) {
            if (channel.name === "Quality Assurance") continue;

            let chan = {
                id: channel.id,
                name: channel.name,
                slug: channel.name.toLowerCase().replace(/[^a-z\d]/gmi, ""),
                web: channel.webUrl,
                builds: []
            }

            console.log("Fetching: " + prj.id + ", deploy channels: " + channel.id);
            const builds = (await axios.get("https://ci.minteck.org/app/rest/buildTypes/id:" + channel.id + "/builds/", { headers: {"Authorization": "Bearer " + token} })).data.build;

            for (const build of builds) {
                if (build.status !== "SUCCESS" || build.state !== "finished") continue;

                let b = {
                    id: build.id,
                    localId: build.number - 1 + 1,
                    date: new Date(build.finishOnAgentDate.substring(0, 4) + "-" + build.finishOnAgentDate.substring(4, 6) + "-" + build.finishOnAgentDate.substring(6, 8) + "T" + build.finishOnAgentDate.substring(9, 11) + ":" + build.finishOnAgentDate.substring(11, 13) + ":" + build.finishOnAgentDate.substring(13, 15) + "+" + build.finishOnAgentDate.substring(16, 18) + ":" + build.finishOnAgentDate.substring(18, 20)).toISOString(),
                    branch: build.branchName,
                    web: build.webUrl,
                    agent: null,
                    artifacts: []
                }

                console.log("Fetching: " + prj.id + ", deploy channels: " + channel.id + ", build: " + build.id + "/" + build.number);
                const artifacts = (await axios.get("https://ci.minteck.org/app/rest/builds/id:" + build.id + "/artifacts/children/", { headers: {"Authorization": "Bearer " + token} })).data.file;

                for (const artifact of artifacts) {
                    let af = {
                        name: artifact.name,
                        size: artifact.size,
                        download: "https://ci.minteck.org" + (artifact.content ? artifact.content.href : artifact.children.href + "/" + artifact.name)
                    }

                    b.artifacts.push(af);
                }

                chan.builds.push(b);
            }

            if (chan.builds.length > 0) {
                prjData.channels.push(chan);
            }
        }

        if (prjData.channels.length > 0) {
            fs.writeFileSync("projects/" + prj.id + ".json", JSON.stringify(prjData, null, 4));
        }
    }
})()