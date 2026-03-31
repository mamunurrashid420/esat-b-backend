pipeline {
    agent { label 'BUILD-SERVER' }
    tools {
        jdk 'JDK17'
        nodejs 'NODE25'
    }
    parameters {
        string(
            name: 'OVERRIDE_TAG',
            defaultValue: '',
            description: 'Optional: override auto-generated image tag'
        )
    }

    environment {
        CONTAINER_NAME        = "eastb-backend"
        DOCKER_FILENAME       = "Dockerfile"
        DOCKER_HUB_USERNAME   = "jamaldevsecops"
        DOCKER_CREDENTIALS_ID = "PersonalDockerHubAccessToken"
        RECIPIENT_EMAILS      = "jamal.devsecops@gmail.com"

        DEP_CHECK_NAME        = "OWASP-DC"

        SONARQUBE_SERVER      = "NGD-SonarQube-Scanner"
        SONAR_PROJECT_KEY     = "eastb-backend"
        SONAR_PROJECT_NAME    = "eastb-backend"
    }

    stages {

        stage('📥 Prepare Git Info') {
            steps {
                script {
                    def gitTag = sh(returnStdout: true, script: "git describe --tags --abbrev=0 || echo ''").trim()
                    def commitHash = sh(returnStdout: true, script: "git rev-parse --short HEAD").trim()

                    if (params.OVERRIDE_TAG?.trim()) {
                        env.IMAGE_TAG = params.OVERRIDE_TAG
                    } else if (gitTag) {
                        env.IMAGE_TAG = gitTag.startsWith('v') ? gitTag.substring(1) : gitTag
                    } else {
                        env.IMAGE_TAG = commitHash
                    }

                    env.GIT_COMMIT_HASH = commitHash

                    env.GIT_BRANCH = sh(
                        returnStdout: true,
                        script: "git rev-parse --abbrev-ref HEAD | sed 's|origin/||'"
                    ).trim()

                    echo "Branch: ${env.GIT_BRANCH}"
                    echo "Commit: ${env.GIT_COMMIT_HASH}"
                    echo "Image Tag: ${env.IMAGE_TAG}"
                }
            }
        }

        stage('🔎 SonarQube SAST Scan') {
            steps {
                script {
        
                    def scannerHome = tool 'NGD-SonarQube-Scanner'
        
                    withSonarQubeEnv('NGD-SonarQube-Scanner') {
        
                        sh """
                        ${scannerHome}/bin/sonar-scanner \
                          -Dsonar.projectKey=${SONAR_PROJECT_KEY} \
                          -Dsonar.projectName=${SONAR_PROJECT_NAME} \
                          -Dsonar.projectVersion=${IMAGE_TAG} \
                          -Dsonar.sources=. \
                          -Dsonar.exclusions=**/.git/**,**/node_modules/**,**/target/**,**/build/** \
                          -Dsonar.scm.revision=${GIT_COMMIT_HASH} \
                          -Dsonar.scanner.skipJreProvisioning=true
                        """
        
                    }
                }
            }
        }

        stage('🚦 SonarQube Quality Gate') {
            steps {
                timeout(time: 10, unit: 'MINUTES') {
                    script {
                        def qg = waitForQualityGate()
        
                        if (qg.status != 'OK') {
                            echo "⚠️ Quality Gate failed: ${qg.status}, but pipeline will continue temporarily."
                        } else {
                            echo "✅ Quality Gate passed"
                        }
                    }
                }
            }
        }
        stage('🔐 Security Scans') {

            parallel {

                stage('🛡️ Trivy Filesystem Scan') {
                    steps {

                        sh '''
                        mkdir -p reports

                        curl -sSL https://raw.githubusercontent.com/aquasecurity/trivy/main/contrib/html.tpl -o html.tpl

                        trivy fs \
                          --exit-code 0 \
                          --severity HIGH,CRITICAL \
                          --format template \
                          --template @html.tpl \
                          -o reports/trivy-filesystem-scan-report.html .
                        '''
                    }

                    post {
                        always {
                            archiveArtifacts artifacts: 'reports/trivy-filesystem-scan-report.html', fingerprint: true
                        }
                    }
                }

                stage('🧩 OWASP Dependency Check') {
                    steps {

                        dependencyCheck additionalArguments: '''
                            --scan ./
                            --format HTML
                            --enableExperimental
                            --exclude **/node_modules/**
                            --exclude **/dist/**
                            --exclude **/build/**
                            --exclude **/.git/**
                            --exclude **/.idea/**
                            --exclude **/.vscode/**
                            --exclude **/venv/**
                            --exclude **/.venv/**
                            --exclude **/vendor/**
                            --exclude **/target/**
                        ''',
                        odcInstallation: "${env.DEP_CHECK_NAME}"
                    }

                    post {
                        always {
                            archiveArtifacts artifacts: 'dependency-check-report*.html', fingerprint: true
                        }
                    }
                }

            }
        }

        stage('🔧 Build & Push Docker Image') {

            steps {

                script {

                    docker.withRegistry("https://index.docker.io/v1/", env.DOCKER_CREDENTIALS_ID) {

                        def imageName = "${env.DOCKER_HUB_USERNAME}/${env.CONTAINER_NAME}:${env.IMAGE_TAG}"

                        echo "Building image ${imageName}"

                        def app = docker.build(imageName, "-f ${env.DOCKER_FILENAME} .")

                        app.push()
                        app.push("latest")

                        env.DOCKER_IMAGE = imageName

                        env.DOCKER_DIGEST = sh(
                            returnStdout: true,
                            script: "docker inspect --format='{{index .RepoDigests 0}}' ${imageName}"
                        ).trim()

                        echo "Docker image pushed: ${env.DOCKER_DIGEST}"
                    }
                }
            }
        }

        stage('🛡️ Trivy Image Scan') {

            steps {

                sh """
                mkdir -p reports

                curl -sSL https://raw.githubusercontent.com/aquasecurity/trivy/main/contrib/html.tpl -o html.tpl

                trivy image \
                  --exit-code 0 \
                  --severity HIGH,CRITICAL \
                  --format template \
                  --template @html.tpl \
                  -o reports/trivy-docker-image-scan-report.html \
                  ${env.DOCKER_IMAGE}
                """
            }

            post {
                always {
                    archiveArtifacts artifacts: 'reports/trivy-docker-image-scan-report.html', fingerprint: true
                }
            }
        }

    }

    post {

        success {
            script {
                emailext(
                    subject: "[SUCCESS] Docker Image (${env.CONTAINER_NAME}:${env.IMAGE_TAG}) Build Status",
                    body: """
                    <html>
                    <body>
                        <h2 style="color:green;">✅ Docker Image Build Successful</h2>
                        <table border="1" cellpadding="6" cellspacing="0">
                            <tr><td><b>Docker Image</b></td><td>${env.DOCKER_IMAGE}</td></tr>
                            <tr><td><b>Digest</b></td><td>${env.DOCKER_DIGEST}</td></tr>
                            <tr><td><b>Git Branch</b></td><td>${env.GIT_BRANCH}</td></tr>
                            <tr><td><b>Git Commit</b></td><td>${env.GIT_COMMIT_HASH}</td></tr>
                        </table>
                        <p style="margin-top: 15px;">
                            <strong>Attached Reports:</strong><br>
                            • <strong>OWASP Dependency-Check</strong> (Identifies vulnerable third-party libraries used by your application - SCA)<br>
                            • <strong>Trivy Image Scan</strong> (Scans container images for known vulnerabilities after build)<br>
                            • <strong>Trivy Filesystem Scan</strong> (Scans source code, configuration files, and local directories)<br>
                        </p>
                    </body>
                    </html>
                    """,
                    mimeType: 'text/html',
                    to: "${env.RECIPIENT_EMAILS}",
                    attachLog: false,
                    attachmentsPattern: 'dependency-check-report.html,reports/trivy-docker-image-scan-report.html,reports/trivy-filesystem-scan-report.html'
                )
            }
        }

        failure {
            script {
                emailext(
                    subject: "[FAILED] Docker Image (${env.CONTAINER_NAME}:${env.IMAGE_TAG}) Build Status",
                    body: """
                    <html>
                    <body>
                        <h2 style="color:red;">⛔ Docker Image Build Failed</h2>
                        <table border="1" cellpadding="6" cellspacing="0">
                            <tr><td><b>Docker Image</b></td><td>${env.DOCKER_IMAGE}</td></tr>
                            <tr><td><b>Build Number</b></td><td>${env.BUILD_NUMBER}</td></tr>
                            <tr><td><b>Git Branch</b></td><td>${env.GIT_BRANCH}</td></tr>
                            <tr><td><b>Git Commit</b></td><td>${env.GIT_COMMIT_HASH}</td></tr>
                            <tr><td><b>Build URL</b></td><td><a href="${env.BUILD_URL}">${env.BUILD_URL}</a></td></tr>
                        </table>
                        <p style="margin-top: 15px;">
                            <strong>Attached Reports:</strong><br>
                            • <strong>Build Logs</strong> (Contains entire logs of the pipeline)<br>
                        </p>
                    </body>
                    </html>
                    """,
                    mimeType: 'text/html',
                    to: "${env.RECIPIENT_EMAILS}",
                    attachLog: true,
                    attachmentsPattern: ''
                )
            }
        }
    }
}
